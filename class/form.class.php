<?php

include_once 'model.class.php';

class Form extends Model
{

    private $dbh;
    private $query;



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
        $tab = explode(' ', strtolower($query));
        if ($tab[0] === 'select') {
            // On cherche dans la requête le mot "from" pour en déduire ensuite le nom de la colonne sur laquelle on travaille
            $tabName = $tab[array_search('from', $tab) + 1];
            // On vérifie si la colonne existe
            if ($this->tabExists($tabName)) {
                // Si elle existe on utilise notre classe Model pour travailler dessus
                $this->setTab($tabName);

                // On transforme le nom des colonnes voulues en tableau pour le manipuler plus tard
                $colNames = explode(',', $tab[1]);

                // On récupère les informations des colonnes de la table
                $sql = "SHOW COLUMNS FROM " . $this->getTab();
                $data = array();
                foreach ($this->getData($sql) as $col) {
                    // On crée un tableau associatif de type : $nomColonne => array $typeColonne (sans alias)
                    $data[$col['Field']] = explode(" ", $col['Type']);
                }

                // On crée un tableau de type : nomColonne => type d'input
                $inputs = array();
                foreach ($data as $key => $val) {
                    if ($this->in_array_like('varchar', $val)) {

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
                    }
                }

                // On doit maintenant faire les modifications en fonction des PK et FK
                foreach($inputs as $key => $val){
                    if($this->is_fk($key)){
                        $inputs[$key] = 'select';
                    }
                }

                // On crée un formulaire
                $print = '<form action="" method="post" class="ui edit user form container">';
                // Pour chaque inputs de type : nomColonne => type d'input, on crée un label
                foreach ($inputs as $key => $val) {

                    // On affiche seulement les colonnes demandées
                    if(in_array($key, $colNames) || $colNames[0] == '*'){
                        $print .= '<div class="field">';
                        $print .= '<label>' . $key . '</label>';
                        // Si l'input et de type text, email, password ou number alors on crée un input simple
                        if ($val !== 'select') {
                            $print .= '<input type="' . $val . '" name="' . $key . '">';
                        } else {
                            // Sinon on crée un select
                            $print .= '<select name="' . $key . '" class="ui search dropdown">';
                        
                            $fks = $this->fk_infos($key);
                            foreach($fks as $fk){
                                $this->setTab($fk["REFERENCED_TABLE_NAME"]);
                                $x = $this->getData("SELECT * FROM " . $fk["REFERENCED_TABLE_NAME"]);
                                foreach ($x as $row) {
                                    $print .= '<option value="' . $row[$this->getPrimary()] . '">' . $row[$this->getPrimary()] . '</option>';
                                }
                            }
                            
                            $print .= '</select>';
                        }

                        $print .= '</div>';
                    }
                }

                $print .= '<input type="submit" value="Envoyer">';
                $print .= '</form>';

                return $print;
            } else {
                throw new Exception(__CLASS__ . ' : La table n\'existe pas');
            }
        } else {
            throw new Exception('La requête doit être de type select');
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
                REFERENCED_TABLE_SCHEMA = '".$this->getDBName()."'
                AND TABLE_NAME = '" . $this->getTab()."'";
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
     * Récupère le nom, la table de référence et la colonne de référence d'un clé étrangère
     * @param string $column : colonne de type foreign_key
     * @return array : tableau associatif
     */
    
    public function fk_infos(string $column)
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
            AND TABLE_NAME = '" . $this->getTab()."'
            AND COLUMN_NAME = '$column'";

            // On retourne alors un tableau associatif de qui contient : Nom de la colonne FK, table de référence, colonne de référence dans cette dernière
            return  $this->getData($sql);
        } else {
            throw new Exception('La colonne selectionnée n\'est pas une clé étrangère');
        }
    }

    public function printSelect(){

    }
}
