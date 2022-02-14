<?php

include_once 'connexion.class.php';

class PrintSQL extends Connexion
{
    private $dbh;
    private $query;
    private $offset;
    private $limit;
    private $range = 8;
    private $currentPage;
    private $nameGet = 'page';
    private $class = 'ui striped center aligned celled selectable sortable table';

    const REGEX_NAME = '/[A-Za-z_]{1,12}/';
    const LIB_SEMANTIC = 'semantic';
    const LIB_BOOTSTRAP = 'bootstrap';


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

    // Mutateurs Table
    public function setQuery(string $newQuery)
    {
        $queryTab = explode(' ', strtolower($newQuery));

        if ($queryTab[0] === 'select' || $queryTab[0] === 'show') {
            $this->query = $newQuery;
        } else {
            throw new Exception(__CLASS__ . ' : La requête n\'est pas de type SELECT');
        }
    }

    public function setLibrary(string $newLibrary)
    {
        $newLibrary = strtolower($newLibrary);
        switch ($newLibrary) {
            case self::LIB_SEMANTIC:
                $this->class = 'ui striped center aligned celled selectable sortable table';
                break;
            case self::LIB_BOOTSTRAP:
                $this->class = 'table table-hover table-striped';
                break;
            default:
                throw new Exception(__CLASS__ . ' : Nom de librairie incorrecte, utilisez plutôt : Semantic ou Bootstrap');
        }
    }

    // Mutateurs mixtes
    public function setLimit(int $newLimit)
    {
        if ($newLimit > 0) {
            $this->limit = $newLimit;
        } else {
            throw new Exception(__CLASS__ . ' : Le nombre de lignes à afficher doit être positif.');
        }
    }

    public function setOffset(int $currentPage)
    {
        if ($currentPage > 0) {
            $this->currentPage = $currentPage;
            $this->offset =  $currentPage * $this->limit - $this->limit;
        } else {
            throw new Exception(__CLASS__ . ' : Le numéro de la page doit être positif.');
        }
    }

    // Mutateurs pagination
    public function setRange(int $newRange)
    {
        if ($newRange > 0) {
            $this->range =  $newRange;
        } else {
            throw new Exception(__CLASS__ . ' : La portée de pagination doit être positif.');
        }
    }

    public function setGetName_page(string $newName)
    {
        if (preg_match(self::REGEX_NAME, $newName)) {
            $this->nameGet = $newName;
        } else {
            throw new Exception(__CLASS__ . ' : Le nom du get pour la page doît faire entre 2 et 12 caractères, et ne doit contenir que des lettres minuscules ou majuscules et des underscore');
        }
    }

    // Accesseurs Table
    public function getQuery(): string
    {
        return $this->query;
    }

    public function getColumns(): array
    {

        try {
            $stmt = $this->dbh->query($this->getQuery());

            $data = array();
            for ($i = 0; $i < $stmt->columnCount(); $i++) {
                array_push($data, $stmt->getColumnMeta($i)['name']);
            }

            return $data;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    public function getClass(): string
    {
        return $this->class;
    }

    // Accesseurs Pagination
    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getRange(): int
    {
        return $this->range;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getName_page(): string
    {
        return $this->nameGet;
    }

    /**
     * Renvoi le code HTML d'un tableau contenant les informations de notre requête sql
     * @param bool $set_ordering : {true} pour activer le tri par colonnes
     * @param bool limit : {true} pour prendre en compte le nombre de lignes par page
     * @param bool $offset : {true} pour prendre en compte une valeur minimale
     * @return string : code HTML
     */

    public function printTab(bool $set_ordering, bool $limit = true, bool $offset = true)
    {
        try {
            $sth = $this->getColumns();

            if (in_array(strtolower($_GET['order']), array_map('strtolower', $sth))) {
                $order = htmlspecialchars($_GET['order']);
            } else {
                $order = $sth[0];
            }

            if (in_array(strtolower($_GET['sort']), array('asc', 'desc'))) {
                $sort = htmlspecialchars($_GET['sort']);
            } else {
                header("location: ?order=$order&sort=asc");
                exit();
            }

            $query = $this->getQuery();
            if ($set_ordering) {
                $query .= " ORDER BY $order $sort";
            }

            if ($limit && !is_null($this->getLimit())) {
                if ($offset && !is_null($this->getOffset())) {
                    $query .= ' LIMIT ' . $this->getOffset() . ', ' . $this->getLimit();
                } else {
                    $query .= ' LIMIT ' . $this->getLimit();
                }
            }

            $this->setOptions(array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NUM
            ));
            
            $stmt = $this->getData($query);


            $sortby = ($sort == 'asc') ? 'desc' : 'asc';
            $sorted = ($sort == 'asc') ? 'descending' : 'ascending';

            $print =  '<table class="' . $this->getClass() . '">';
            $print .= '<thead>';
            $print .= '<tr>';
            foreach ($sth as $col) {
                if ($set_ordering) {
                    ($col == $order) ?
                        $print .= "<th class=\"sorted $sorted\"><a href=\"?order=$col&sort=$sortby\">$col</a></th>" :
                        $print .= "<th><a href=\"?order=$col&sort=$sortby\">$col</a></th>";
                } else {
                    $print .= "<th>$col</th>";
                }
            }
            
            $print .= '<th>Modifier</th>';
            $print .= '<th>Supprimer</th>';
            $print .= '</tr>';
            $print .= '</thead>';
            
            $print .= '<tbody>';
            foreach ($stmt as $row) {
                $print .= '<tr>';
                $id = array();
                foreach ($row as $key => $val) {
                    $print .= '<td>' . $val . '</td>';
                    array_push($id, $val);
                }

                $print .= '<td style="text-align:center;"><i data-id="' . reset($row) . '" class="edit outline icon"></i></td>';
                $print .= '<td style="text-align:center;"><i data-id="' . reset($row). '" class="trash alternate icon"></i></td>';

                $print .= '</tr>';
            }
            $print .= '</tbody>';

            $print .= '<tfoot class="full-width">';
            $print .= '<tr>';
            $print .= '<th colspan="'.(count($sth)+1).'">';
            ($limit && !is_null($this->getLimit()) && $offset && !is_null($this->getOffset())) ? $print .= $this->printPaging() : '';
            $print .= '</th>';
            $print .= '<th>';
            $print .= '<button class="ui right labeled icon basic create button"><i class="plus icon"></i>Ajouter</button>';
            $print .= '</th>';
            $print .= '</tr>';
            $print .= '</tfoot>';

            $print .= '</table>';

            return $print;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * Renvoi le code HTML de la pagination pour la requête sql
     * @param bool $add_sides_links : {true} pour ajouter les boutons 'Page précédente' et 'Page suivante'
     * @return string : code HTML des boutons de pagination
     */

    public function printPaging(bool $add_sides_links = true): string
    {
        $sideLinks = floor($this->getRange() / 2);
        $breakLink_start = $sideLinks + 1;
        $linksBeforeBreak_start = $this->getRange() + 1;
        $breakLink_end = $this->getLastLink() - $sideLinks;
        $linksBefore_active = $this->getCurrentPage() - $sideLinks;
        $linksAfter_active = $this->getCurrentPage() + $sideLinks;
        $linksAfterBreak_end = $this->getLastLink() - $this->getRange();

        $print = '<div class="ui pagination menu">';

        if ($add_sides_links) {
            $print .= $this->printPrevious();
        }

        if ($this->getCurrentPage() <= $breakLink_start) {
            for ($i = 1; $i <= $linksBeforeBreak_start; $i++) {
                $_GET[$this->getName_page()] = $i;
                $query_result = http_build_query($_GET);
                $active = ($this->getCurrentPage() == $i) ? ' active' : ' ';
                $print .= '<a href="' . $_SERVER['PHP_SELF'] . '?' . $query_result . '" class="item ' . $active . '">' . $i . '</a>';
            }
        } elseif ($this->getCurrentPage() > $breakLink_start && $this->getCurrentPage() <= $breakLink_end) {
            for ($i = $linksBefore_active; $i <= $linksAfter_active; $i++) {
                $_GET[$this->getName_page()] = $i;
                $query_result = http_build_query($_GET);
                $active = ($this->getCurrentPage() == $i) ? ' active' : ' ';
                $print .= '<a href="' . $_SERVER['PHP_SELF'] . '?' . $query_result . '" class="item ' . $active . '">' . $i . '</a>';
            }
        } else {
            for ($i = $linksAfterBreak_end; $i <= $this->getLastLink(); $i++) {
                $_GET[$this->getName_page()] = $i;
                $query_result = http_build_query($_GET);
                $active = ($this->getCurrentPage() == $i) ? ' active' : ' ';
                $print .= '<a href="' . $_SERVER['PHP_SELF'] . '?' . $query_result . '" class="item ' . $active . '">' . $i . '</a>';
            }
        }

        if ($add_sides_links) {
            $print .= $this->printNext();
        }

        $print .= '</div>';

        return $print;
    }

    /**
     * Renvoi le code HTML des boutons 'Page précédente' et 'Première page'
     * @return string
     */

    private function printPrevious(): string
    {
        if ($this->getCurrentPage() == 1) {
            $toFirstPage = 'disabled';
        } else {
            $_GET[$this->getName_page()] = '1';
            $query_result = http_build_query($_GET);
            $toFirstPage = '" href="' . $_SERVER['PHP_SELF'] . '?' . $query_result . '"';
        }

        if ($this->getCurrentPage() == 1) {
            $toPreviousPage = 'disabled';
        } else {
            $_GET[$this->getName_page()] = strval($this->getCurrentPage() - 1);
            $query_result = http_build_query($_GET);
            $toPreviousPage = '" href="' . $_SERVER['PHP_SELF'] . '?' . $query_result . '"';
        }

        $print = ' <a class="icon item ' . $toFirstPage . '">';
        $print .= '<i class=" angle double left icon"></i>';
        $print .= '</a>';
        $print .= '<a class="icon item ' . $toPreviousPage . '">';
        $print .= '<i class="angle left icon"></i>';
        $print .= '</a>';

        return $print;
    }


    /**
     * Renvoi le code HTML des boutons 'Page suivante' et 'Dernière page'
     * @return string
     */

    private function printNext(): string
    {
        if ($this->getCurrentPage() == $this->getLastLink()) {
            $toNextPage = 'disabled';
        } else {
            $_GET[$this->getName_page()] = strval($this->getCurrentPage() + 1);
            $query_result = http_build_query($_GET);
            $toNextPage = '" href="' . $_SERVER['PHP_SELF'] . '?' . $query_result . '"';
        }

        if ($this->getCurrentPage() == $this->getLastLink()) {
            $toLastPage = 'disabled';
        } else {
            $_GET[$this->getName_page()] = strval($this->getLastLink());
            $query_result = http_build_query($_GET);
            $toLastPage = '" href="' . $_SERVER['PHP_SELF'] . '?' . $query_result . '"';
        }

        $print = '<a class="icon item ' . $toNextPage . '">';
        $print .= '<i class="angle right icon"></i>';
        $print .= '</a>';
        $print .= '<a class="icon item ' . $toLastPage . '">';
        $print .= '<i class="angle double right icon"></i>';
        $print .= '</a>';

        return $print;
    }

    /**
     * Renvoi le numéro du dernier bouton en fonction du nombre de données
     * @return int
     */

    private function getLastLink(): int
    {
        try {

            $stmt = $this->dbh->query($this->getQuery());
            return ceil($stmt->rowCount() / $this->getLimit());
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }
}
