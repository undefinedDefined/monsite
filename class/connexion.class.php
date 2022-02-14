<?php

class Connexion
{

    private $engine;
    private $host;
    private $port;
    private $dbname;
    private $user;
    private $pass;
    private $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    );

    private $dsn;
    private $dbh;
    private $connected = false;

    // Constantes de classe
    const REGEX_HOST = '/([0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3})|([a-z]{1,30})/';
    const REGEX_OBJECT = '/[A-Za-z_]{1,30}/';

    const ENGINE_MYSQL = 'mysql';
    const ENGINE_MARIADB = 'mariadb';
    const ENGINE_POSTGRESQL = 'postgresql';
    const ENGINE_SQLITE = 'sqlite';

    public function __construct()
    {
        // Constructeur vide -> On peut appeler notre class sans paramètres
    }

    /**
     * Configure une connexion de type PDO à notre base de données
     * @param string $newEngine : Type de base de données à connecter
     * @param string $newHost : nom du domaine
     * @param string $newDBName : nom de la base de données
     * @param string $newUser : nom d'utilisateur pour accéder à la bdd
     * @param string $newPass : mot de passe pour accéder à la bdd
     * @param int $newPort : port de connexion
     */

    public function setConfig(
        string $newEngine,
        string $newHost,
        string $newDBName,
        string $newUser,
        string $newPass,
        int $newPort = 3306
    ) {
        // Assigne la valeur de chaque argument à son attribut idoine
        $this->setEngine($newEngine);
        $this->setHost($newHost);
        $this->setPort($newPort);
        $this->setDBName($newDBName);
        $this->setUser($newUser);
        $this->setPass($newPass);
    }

    /**
     * Vérifie si une configuration est active
     * @return bool
     */

    public function checkConfig(): bool
    {
        return (isset($this->engine) && isset($this->host) && isset($this->dbname)) ? true : false;
    }

    /**
     * Se connecte à la base de données grâce aux paramètres de connexion
     * @return PDO
     */

    public function connect(): PDO
    {
        if (!isset($this->dbh) && $this->connected == false) {
            if ($this->checkConfig()) {
                try {

                    // Selon le moteur de BDD
                    switch ($this->getEngine()) {
                        case self::ENGINE_MYSQL:
                        case self::ENGINE_MARIADB:
                            $this->dbh = new PDO(sprintf($this->dsn, $this->getHost(), $this->getDBName()), $this->getUser(), $this->getPass(), $this->getOptions());
                            break;
                        case self::ENGINE_POSTGRESQL:
                            $this->dbh = new PDO(sprintf($this->dsn, $this->getHost(), $this->getPort(), $this->getDBName(), $this->getUser(), $this->getPass()));
                            break;
                        case self::ENGINE_SQLITE:
                            $this->dbh = new PDO(sprintf($this->dsn, $this->getHost()));
                            break;
                        default:
                            throw new PDOException(__CLASS__ . ' : La connexion a échoué.');
                    }

                    $this->connected = true;

                    return $this->dbh;
                } catch (PDOException $e) {
                    throw new Exception('Erreur PDO : ' . $e->getMessage());
                }
            } else {
                throw new Exception(__CLASS__ . ' : Aucune configuration de connexion trouvée.');
            }
        } else {
            throw new Exception(__CLASS__ . ' : Une connexion est déjà active.');
        }
    }

    /**
     * Deconnecte la connexion si elle est active
     */

    public function disconnect()
    {
        if (isset($this->dbh) && $this->connected == true) {

            unset($this->dbh);

            $this->connected = false;
        } else {
            throw new Exception(__CLASS__ . ' : Aucune connexion active.');
        }
    }

    /**
     * Vérifie si une table existe dans la base de données
     * @return bool
     */

    public function tabExists(string $table): bool
    {

        try {
            $query = 'SHOW TABLES LIKE ?';
            $stmt = $this->dbh->prepare($query);
            $stmt->execute(array($table));

            return ($stmt->rowCount() > 0) ? true : false;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * Récupère les données d'une requête sql dans la base de données
     * @return array : tableau associatif
     */

    public function getData(string $sql, array $params = array()): array
    {
        $sqlTab = explode(' ', strtolower($sql));

        if ($sqlTab[0] === 'select' || $sqlTab[0] === 'show') {

            try {
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute($params);

                return $stmt->fetchAll();
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage());
            }
        } else {
            throw new Exception(__CLASS__ . ' : La requête n\'est pas de type SELECT');
        }
    }

    // Mutateurs :
    public function setHost(string $newHost)
    {
        // Teste si $newHost matche avec le RegEx
        if (preg_match(self::REGEX_HOST, $newHost) === 1) {
            $this->host = $newHost;
        } else {
            throw new Exception(__CLASS__ . ' : La valeur de host est incorrecte : ' . self::REGEX_HOST);
        }
    }

    public function setPort(int $newPort)
    {
        if ($newPort > 0 && $newPort < 65537) {
            $this->port = $newPort;
        } else {
            throw new Exception(__CLASS__ . ' : Numéro du port invalide.');
        }
    }

    public function setDBName(string $newDBName)
    {
        if (preg_match(self::REGEX_OBJECT, $newDBName) === 1) {
            $this->dbname = $newDBName;
        } else {
            throw new Exception(__CLASS__ . ' : La valeur de dbname est incorrecte : ' . self::REGEX_OBJECT);
        }
    }

    public function setUser(string $newUser)
    {
        if (preg_match(self::REGEX_OBJECT, $newUser) === 1) {
            $this->user = $newUser;
        } else {
            throw new Exception(__CLASS__ . ' : La valeur du username est incorrecte : ' . self::REGEX_OBJECT);
        }
    }

    public function setPass(string $newPass)
    {
        $this->pass = $newPass;
    }

    public function setOptions(array $newOptions)
    {
        $this->options = $newOptions;
    }

    public function setEngine(string $newEngine)
    {
        // Selon le moteur choisi, génère la DSN correspondante
        $newEngine = strtolower($newEngine);
        switch ($newEngine) {
            case self::ENGINE_MYSQL:
            case self::ENGINE_MARIADB:
                $this->engine = $newEngine;
                $this->dsn = 'mysql:host=%s;dbname=%s;charset=utf8';
                break;
            case self::ENGINE_POSTGRESQL:
                $this->engine = $newEngine;
                $this->dsn = 'pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s;charset=utf8';
                break;
            case self::ENGINE_SQLITE:
                $this->engine = $newEngine;
                $this->dsn = 'sqlite:%s';
                break;
            default:
                throw new Exception(__CLASS__ . ' : Valeur de engine incorrecte, utiliser plutôt : MySQL, Mariadb, PostgreSQL ou SQLite.');
        }
    }


    // Accesseurs
    public function getEngine(): string
    {
        return $this->engine;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getDBName(): string
    {
        return $this->dbname;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPass(): string
    {
        return $this->pass;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
