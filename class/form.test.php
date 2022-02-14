<?php

include_once 'model.class.php';

class Test extends Model
{

    public function __construct(
        string $newEngine,
        string $newHost,
        string $newDBName,
        string $newUser,
        string $newPass,
        int $newPort = 3306
    ) {
        $this->setConfig($newEngine, $newHost, $newDBName, $newUser, $newPass, $newPort);
        $this->dbh = $this->connect();
    }

    public function printForm(string $query)
    {
        // On travaille la requête pour la découper et en tirer le nom des colonnes et de la table selectionnés
        // On assainit la requête
        $query = htmlspecialchars($query);
        // On remplace les virgules (entre le nom des colonnes) par un espace
        $query = str_replace(',', ' ', strtolower($query));
        // On transforme la string en tableau entre chaque espace
        $tab = explode(' ', $query);

        // On vérifie que la requête est bien de type Select avant de continuer
        if ($tab[0] !== 'select') {
            throw new Exception('La requête doit être de type select');
            return false;
        }

        // On instancie un tableau vide qui contiendra le nom des colonnes
        $colNames = array();
        // On récupère les valeurs du tableau pour tous les indexes entre le 'select' et le 'from' 
        for ($i = 1; $i < array_search('from', $tab); $i++) {
            array_push($colNames, $tab[$i]);
        }
        // On supprime les eventuelles valeurs vides, nulles, ou invalides
        $colNames = array_filter($colNames);

        // On déduit à partir de la position du 'from' le nom de la table sur laquelle on travaille
        $tabName = $tab[array_search('from', $tab) + 1];

        // On vérifie si la table existe
        if ($this->tabExists($tabName)) {
            // Si elle existe on utilise notre classe Model pour travailler dessus
            $this->setTab($tabName);

            // On appelle notre fonction qui renvoi un tableau associatif avec le nom et le type d'input
            $inputs = $this->checkColumns();

            // On crée un formulaire
            $print = '<form action="" method="post" class="ui edit user form container">';
            // Pour chaque inputs de type : nomColonne => type d'input, on crée un label
            foreach ($inputs as $key => $val) {
                $this->setTab($tabName);
                // On affiche seulement les colonnes demandées
                if (in_array($key, $colNames) || $colNames[0] == '*') {
                    $print .= '<div class="field">';
                    ($val != 'hidden') ? $print .= '<label>' . $key . '</label>' : '';

                    // test pour décentraliser la création de select et pouvoir utiliser la récursivité
                    switch ($val) {
                        case 'select':
                            // On récupère les informations sur la foreign_key
                            $infos = $this->fk_infos($key);
                            // On crée un select pour chaque foreign_key
                            $print .= '<select name="' . $key . '">';
                            // On appelle notre fonction printOptions qui va envoyer le code html des balises options
                            // à partir des tables de référence de nos foreign_keys
                            $print .= $this->printOptions($infos[0]["REFERENCED_TABLE_NAME"]);
                            $print .= '</select>';
                            // Attention à bien reconfigurer la table sur laquelle on travaille
                            $this->setTab($tabName);
                            break;
                        default:
                            $print .= '<input type="' . $val . '" name="' . $key . '">';
                            break;
                    }

                    $print .= '</div>';
                }
            }

            $print .= '<button class="ui button" type="submit">Envoyer</button>';
            $print .= '</form>';

            return $print;
        } else {
            throw new Exception(__CLASS__ . ' : La table n\'existe pas');
        }
    }

    /**
     * Vérifie si une valeur type %val% est présente dans le tableau
     * @param string $toFind : mot à rechercher dans le tableau
     * @param array $array : tableau dans lequel chercher la valeur
     * @return bool : true si une valeur correspondante est trouvée, false sinon
     */

    private function in_array_like(string $toFind, array $array)
    {
        foreach ($array as $vals) {
            if (stripos($vals, $toFind) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si une colonne est une clée étrangère dans sa table
     * @param string $column : nom de la colonne à vérifier
     * @return bool : true si c'est une foreign_key, false sinon
     */

    public function is_fk(string $column)
    {

        $sql = "SELECT 
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
                FROM
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE
                REFERENCED_TABLE_SCHEMA = '" . $this->getDBName() . "'
                AND TABLE_NAME = '" . $this->getTab() . "'";
        // On a alors un tableau associatif de qui contient : Nom de la colonne FK, table de référence, colonne de référence dans cette dernière
        $fk = $this->getData($sql);

        foreach ($fk as $col) {
            if ($col['COLUMN_NAME'] == $column) {
                return true;
            }
        }

        return false;
    }

    /**
     * Récupère le nom, la table de référence et la colonne de référence d'une clé étrangère
     * @param string $column : colonne de type foreign_key
     * @return array : tableau associatif
     */

    private function fk_infos(string $column)
    {
        if ($this->is_fk($column)) {
            $sql = "SELECT 
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
            FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE
            REFERENCED_TABLE_SCHEMA = '" . $this->getDBName() . "'
            AND TABLE_NAME = '" . $this->getTab() . "'
            AND COLUMN_NAME = '$column'";

            // On retourne alors un tableau associatif de qui contient : Nom de la colonne FK, table de référence, colonne de référence dans cette dernière
            return  $this->getData($sql);
        } else {
            throw new Exception('La colonne selectionnée n\'est pas une clé étrangère');
        }
    }

    /**
     * Vérifie les informations des colonnes de la table en cours,
     * mais également les colonnes qui sont foreign_key et leur assigne comme type d'input 'select'
     * @return array : tableau associatif de type nomColonne => typeInput
     */

    private function checkColumns(): array
    {
        // On récupère les informations des colonnes de la table
        $sql = "SHOW COLUMNS FROM " . $this->getTab();
        $data = array();
        foreach ($this->getData($sql) as $col) {
            // On crée un tableau associatif de type : $nomColonne => array $typeColonne (sans alias)
            $data[$col['Field']] = explode(" ", $col['Type']);
        }

        // On crée un tableau de type : nomColonne => typeInput
        $inputs = array();
        foreach ($data as $key => $val) {
            if ($this->in_array_like('char', $val) || $this->in_array_like('text', $val)) {

                switch ($key) {
                    case 'email':
                        $inputs[$key] = 'email';
                        break;
                    case 'password':
                        $inputs[$key] = 'password';
                    default:
                        $inputs[$key] = 'text';
                        break;
                }
            } elseif ($this->in_array_like('int', $val)) {
                $inputs[$key] = 'number';
            } else {
                $inputs[$key] = 'undefined';
            }
        }

        // On doit maintenant faire les modifications en fonction des PK et FK
        foreach ($inputs as $key => $val) {
            if ($this->is_fk($key)) {
                $inputs[$key] = 'select';
            } elseif ($key == $this->getPrimary()) {
                $inputs[$key] = 'hidden';
            }
        }

        return $inputs;
    }

    /**
     * Renvoi les options dans le cas d'un select
     * @param string $table : table sur laquelle on veut récupérer les informations
     * @return string : code html des options à mettre dans un select
     */

    private function printOptions(string $table)
    {
        // On utilise la table passée en argument
        $table = htmlspecialchars($table);
        $this->setTab($table);

        // On récupère les informations des colonnes de notre table
        $inputs = $this->checkColumns();

        // On cherche l'index de la première colonne de type varchar de notre table
        $description = array_search('text', $inputs);
        $index = array_search('select', $inputs);

        // On récupère toutes les informations de notre table
        $query = "SELECT * FROM " . $table;
        $data = $this->getData($query);

        // Trois cas : soit la table possède une colonne de type varchar auquel cas on ajoute son contenu dans notre option
        // Soit la table n'en possède pas, et dans ce cas on relance la fonction avec la première Foreign_key de la table
        // Si elle ne possède ni l'un ni l'autre alors on affiche la valeur de la PK
        $print = '';
        if ($description) {
            foreach ($data as $row) {
                $print .= '<option value="' . $row[$this->getPrimary()] . '">' . $row[$description] . '</option>';
            }
        } elseif ($index) {
            $infos = $this->fk_infos($index);
            $print .= $this->printOptions($infos[0]['REFERENCED_TABLE_NAME']);
        } else {
            foreach ($data as $row) {
                $print .= '<option value="' . $row[$this->getPrimary()] . '">' . $row[$this->getPrimary()] . '</option>';
            }
        }

        return $print;
    }
}
