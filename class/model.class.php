<?php

include_once 'connexion.class.php';

class Model extends Connexion
{

    private $tab;
    private $dbh;

    public function __construct(
        string $newEngine,
        string $newHost,
        string $newDBName,
        string $newUser,
        string $newPass,
        int $newPort = 3306
    ) {
        $this->setConfig($newEngine, $newHost, $newDBName, $newUser, $newPass, $newPort);
        $this->connect();

    }

    /**
     * Récupère le nom de la colonne primaire
     * @return string
     */

    public function getPrimary(): string
    {
        try {
            $query = 'SHOW KEYS FROM ' . $this->getTab() . ' WHERE Key_name = \'PRIMARY\'';
            // return parent::getData($query)[0]['Column_name'];

            $stmt = $this->getPDO()->query($query);

            return $stmt->fetch()['Column_name'];
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * Vérifie si une colonne existe dans la table en cours
     * @return bool
     */

    public function colExists(string $col): bool
    {
        try {
            $query = 'SHOW COLUMNS FROM ' . $this->getTab() . ' WHERE Field = ?';

            $param = array($col);
            return !empty($this->getData($query, $param));

            // $stmt = $this->getPDO()->prepare($query);
            // $stmt->execute(array($col));

            // return ($stmt->rowCount() > 0) ? true : false;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * Récupère les informations de la ligne telle que  $col = $val
     * @param string $col : nom de la colonne pour laquelle matcher la valeur
     * @param string $val : valeur à matcher
     * @return array : tableau associatif
     */

    public function getRow(string $col, string $val): array
    {
        try {
            // $query = 'SELECT * FROM ' . $this->getTab() . ' WHERE ' . $this->getPrimary() . ' = ?';
            $query = 'SELECT * FROM ' . $this->getTab() . ' WHERE ' . $col . ' = ?';
            $param = array($val);

            return $this->getData($query, $param);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * Récupère les information de la ligne avec un id spécifique
     * @param int $id : ID de la ligne à rechercher dans la table
     * @return array : tableau associatif
     */

    public function getRowFromId(int $id): array
    {
        try {

            return $this->getRow($this->getPrimary(), $id);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * Récupère les informations de toutes les colonnes passées en paramètre
     * @param array $cols : tableau indéxé avec le nom des colonnes / laisser vide si on veut toutes les colonnes dispo
     * @return array : tableau associatif
     */

    public function getRows(array $cols = array()): array
    {

        if (!empty($cols)) {

            foreach ($cols as $col) {
                if (!$this->colExists($col)) {
                    throw new Exception('La colonne ' . $col . ' n\'existe pas');
                }
            }

            $query = 'SELECT %s FROM ' . $this->getTab();
            $query = sprintf(
                $query,
                implode(',', $cols)
            );
        } else {
            $query = 'SELECT * FROM ' . $this->getTab();
        }

        try {

            return $this->getData($query);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * Récupère le nom de toutes les colonnes
     * @return array : tableau indéxé
     */

    public function getColumns(): array
    {

        try {
            $query = "SHOW COLUMNS FROM " . $this->getTab();

            $data = array();
            foreach($this->getData($query) as $col){
                array_push($data, $col["Field"]);
            }

            return $data;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * Ajoute une ligne dans la table en cours
     * @param array $data : tableau associatif tel que ['colonne1' => 'valeur1']
     * @return bool
     */

    public function insert(array $data, bool $getid = false): mixed
    {
        if (!empty($data)) {
            foreach ($data as $key => $val) {
                if ($this->colExists($key)) {
                    $params[":$key"] = htmlspecialchars($val);
                } else {
                    throw new Exception('La colonne ' . $key . ' n\'existe pas');
                }
            }

            try {
                $query = 'INSERT INTO ' . $this->getTab() . ' (%s) VALUES (%s)';
                $query = sprintf(
                    $query,
                    implode(', ', array_keys($data)),
                    implode(', ', array_keys($params))
                );

                $stmt = $this->getPDO()->prepare($query);
                $res = $stmt->execute($params);

                return (!$getid) ?
                $res : $this->getPDO()->lastInsertId();

            } catch (PDOException $e) {
                throw new PDOException($e->getMessage());
            }
        } else {
            throw new Exception('Le tableau ne peut pas être vide');
        }
    }

    /**
     * Met à jour les données d'une ligne en fonction de son ID
     * @param array $data : tableau associatif tel que ['colonne1' => 'valeur1'] des colonnes et valeurs à mettre à jour
     * @param int $id : ID de la ligne à modifier
     * @return bool
     */

    public function update(array $data, int $id): bool
    {

        if (!empty($data)) {

            foreach ($data as $key => $val) {
                if ($this->colExists($key)) {
                    $vals[] = "$key = :$key";
                    $params[":$key"] = htmlspecialchars($val);
                } else {
                    throw new Exception(__CLASS__ . ' : La colonne ' . $key . ' n\'existe pas.');
                }
            }

            $params[':id'] = htmlspecialchars($id);

            try {
                $query = 'UPDATE ' . $this->getTab() . ' SET ' . implode(', ', $vals) . ' WHERE ' . $this->getPrimary() . ' = :id';
                $stmt = $this->getPDO()->prepare($query);

                return $stmt->execute($params);
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage());
            }
        } else {
            throw new Exception('Le tableau ne peut pas être vide');
        }
    }

    /**
     * Supprime une ligne en fonction de son ID
     * @param int $id : ID de la ligne à supprimer
     * @return bool
     */

    public function delete(int $id): bool
    {
        try {
            $query = 'DELETE FROM ' . $this->getTab() . ' WHERE ' . $this->getPrimary() . ' = ?';
            $stmt = $this->getPDO()->prepare($query);
            return $stmt->execute(array($id));
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    // Accesseur
    public function getTab(){

        return $this->tab;
    }

    // Mutateur
    public function setTab(string $newTab)
    {
        if ($this->tabExists($newTab)) {
            $this->tab = $newTab;
        } else {
            throw new Exception(__CLASS__ . ' : La table demandée n\'existe pas.');
        }
    }
}
