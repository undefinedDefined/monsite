<?php

include_once 'globals.php';


class Table
{
    /**
     * * Fonction qui instancie une nouvelle connection à notre base de données
     */

    public function connection()
    {
        $dbh = new PDO(
            'mysql:host=' . SERVER . ';port=' . PORT . ';dbname=' . DBB . ';charset=utf8',
            USER,
            PASS,
            PDO_OPTIONS
        );

        return $dbh;
    }


    /**
     * * Fonction qui retourne le nom des colonnes d'une requête SQL
     * @param string @sql : requête SQL pour laquelle on veut récupérer le nom des colonnes
     * 
     * @return array : Tableau indéxé contenant le nom de toutes les colonnes
     */

    public function get_columns_names(string $sql)
    {
        $dbh = $this->connection();

        $stmt = $dbh->prepare($sql);

        $stmt->execute();

        $donnees = array();

        $columnCount = $stmt->columnCount();

        for ($i = 0; $i < $columnCount; $i++) {
            $columnInfo = $stmt->getColumnMeta($i);
            array_push($donnees, $columnInfo['name']);
        }

        return $donnees;
    }


    /**
     * * Fonction qui donne la première valeur à afficher (offset) en fonction de la page sur laquelle on se trouve
     * * à utiliser dans le cadre d'une pagination
     * @param int $currentPage : Page sur laquelle on se trouve, pour laquelle calculer la première query à afficher
     * 
     * @return int : offset de la première valeur à afficher dans la requête SQL
     */

    public function get_paging_offset(int $currentPage)
    {
        return (int) 0 + (($currentPage - 1) * 10);
    }


    /**
     * * Fonction qui retourne les informations nécessaire à la gestion des données tirées d'une requête SQL
     * @param string $sql : Requête SQL de type "SELECT * FROM table" à partir de laquelle on veut récupérer les informations
     * @param string $orderName : Order de tri en fonction du nom (par défaut : première colonne)
     * @param string $orderBy : Order de tri croissant ou décroissant (ASC or DESC) => par défaut ASC
     * @param int $offset : Numéro de ligne à partir de laquelle afficher les données
     * @param int $limit : Nombre de lignes à afficher à partir de la ligne selectionnée
     * 
     * @return PDOStatement : Tableau indexé contenant toutes les lignes demandées
     */

    public function get_table_infos(string $sql, string $orderName = null, string $orderBy = null, int $offset = 0, int $limit = null)
    {
        $dbh = $this->connection();

        // Getting column names to match them with $orderName requested
        $columnName = $this->get_columns_names($sql);
        $sortCondition = ['asc', 'ASC', 'desc', 'DESC'];
        // If no $orderName requested, or empty value, we sort sql query from the first column
        if (is_null($orderName) || empty($orderName)) {
            $orderName = $columnName[0];
        }
        // If no $orderBy requested, or empty value, we sort sql query crescently
        if (is_null($orderBy) || empty($orderBy)) {
            $orderBy = 'ASC';
        }
        // Concatenate ORDER BY and LIMIT if requested, and correct values
        $request = $sql;
        if (in_array($orderName, $columnName) && in_array($orderBy, $sortCondition)) {
            $request .= " ORDER BY $orderName $orderBy";
            (!is_null($limit) && $limit > 0) ? $request .= " LIMIT $offset, $limit" : '';
        }

        $stmt = $dbh->prepare($request);

        $stmt->execute();

        $donnees = $stmt->fetchAll(PDO::FETCH_NUM);

        return $donnees;
    }


    /**
     * * Fonction privée qui permet de calculer les informations nécessaires à la pagination
     * 
     * @param string $sql : Requête SQL pour laquelle on veut une pagination
     * @param int $currentPage : Numéro de la page sur laquelle on se trouve, nécessaire pour calculer le nombre de boutons à afficher
     * @param int $range : Nombre de boutons à afficher visuellement dans la pagination
     * @param int $perPage : Nombre de lignes de notre requête SQL qu'on veut afficher par page
     * 
     * @return array
     */

    private function get_paging_infos(string $sql, int $currentPage = 1, int $range = 8, int $perPage = 10)
    {
        $dbh = $this->connection();

        $stmt = $dbh->prepare($sql);

        $stmt->execute();;
        if ($perPage > 0) {

            $sidePages = (int) floor($range / 2);
            $lastPage = (int) ceil($stmt->rowCount() / $perPage);

            $breakPageBottom = (int) $sidePages + 1;
            $countPagesBottom = (int) $range + 1;
            $breakPageTop = (int) $lastPage - $sidePages;
            $botSidePagesCount = (int) $currentPage - $sidePages;
            $topSidePagesCount = (int) $currentPage + $sidePages;
            $countPagesTop =  (int) $lastPage - $range;

            $donnees = array(
                'page_break_bot' => $breakPageBottom,
                'pages_count_bot' => $countPagesBottom,
                'page_break_top' => $breakPageTop,
                'side_pages_count_bot' => $botSidePagesCount,
                'side_pages_count_top' => $topSidePagesCount,
                'pages_count_top' => $countPagesTop,
                'last_page' => $lastPage
            );
        }

        return $donnees;
    }

    
    /**
     * *Fonction qui permet d'imprimer la pagination liée à notre requête SQL
     * 
     * @param string $sql : la requête SQL à prendre en compte pour le calcul de la pagination
     * @param int $currentpage : Page sur laquelle on se trouve
     * @param int $range : Nombre de boutons à afficher visuellement dans la pagination (défault 8)
     * @param int $perPage : Nombre de lignes de notre requête SQL qu'on veut afficher par page (défault 10)
     * 
     * @return html : code HTML de notre pagination avec liens en méthode $_GET (qui mènent vers ?page=x )
     */

    public function print_paging(string $sql, int $currentPage, int $range = 8, int $perPage = 10)
    {

        if($perPage > 0 && $range > 0){
            $paging = $this->get_paging_infos($sql, $currentPage, $range, $perPage);
        }else{
            throw new Exception('Le nombre d\'elements par page et/ou le taille de pagination renseigné est incorrect ');
        }

        $query = $_GET;


        $donnees = '';

        if ($currentPage == 1) {
            $toFirstPage = 'disabled';
        } else {
            $query['page'] = '1';
            $query_result = http_build_query($query);
            $toFirstPage = '" href="' . $_SERVER['PHP_SELF'] . '?' . $query_result . '"';
        }

        if ($currentPage == 1) {
            $toPreviousPage = 'disabled';
        } else {
            $query['page'] = strval($currentPage - 1);
            $query_result = http_build_query($query);
            $toPreviousPage = '" href="' . $_SERVER['PHP_SELF'] . '?' . $query_result . '"';
        }

        if ($currentPage == $paging['last_page']) {
            $toNextPage = 'disabled';
        } else {
            $query['page'] = strval($currentPage - 1);
            $query_result = http_build_query($query);
            $toNextPage = '" href="' . $_SERVER['PHP_SELF'] . '?' . $query_result . '"';
        }

        if ($currentPage == $paging['last_page']) {
            $toLastPage = 'disabled';
        } else {
            $query['page'] = strval($paging['last_page']);
            $query_result = http_build_query($query);
            $toLastPage = '" href="' . $_SERVER['PHP_SELF'] . '?' . $query_result . '"';
        }

        $donnees .= '<a class="icon item ' . $toFirstPage . '">
                                <i class=" angle double left icon"></i>
                            </a>
                            <a class="icon item ' . $toPreviousPage . '">
                                <i class="angle left icon"></i>
                            </a>';


        if ($currentPage <= $paging['page_break_bot']) {
            for ($i = 1; $i <= $paging['pages_count_bot']; $i++) {
                $query['page'] = $i;
                $query_result = http_build_query($query);
                $active = ($currentPage == $i) ? ' active' : ' ';
                $donnees .= '<a href="' . $_SERVER['PHP_SELF'] . '?' . $query_result . '" class="item ' . $active . '">' . $i . '</a>';
            }
        } elseif ($currentPage > $paging['page_break_bot'] && $currentPage <= $paging['page_break_top']) {
            for ($i = $paging['side_pages_count_bot']; $i <= $paging['side_pages_count_top']; $i++) {
                $query['page'] = $i;
                $query_result = http_build_query($query);
                $active = ($currentPage == $i) ? ' active' : ' ';
                $donnees .= '<a href="' . $_SERVER['PHP_SELF'] . '?' . $query_result . '" class="item ' . $active . '">' . $i . '</a>';
            }
        } else {
            for ($i = $paging['pages_count_top']; $i <= $paging['last_page']; $i++) {
                $query['page'] = $i;
                $query_result = http_build_query($query);
                $active = ($currentPage == $i) ? ' active' : ' ';
                $donnees .= '<a href="' . $_SERVER['PHP_SELF'] . '?' . $query_result . '" class="item ' . $active . '">' . $i . '</a>';
            }
        }

        $donnees .= ' <a class="icon item ' . $toNextPage . '">
                                <i class="angle right icon"></i>
                            </a>
                            <a class="icon item ' . $toLastPage . '">
                                <i class="angle double right icon"></i>
                            </a>';

        return $donnees;
    }
}
