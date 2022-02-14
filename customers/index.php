<?php

include_once "../inc/globals.php";
include_once "../class/print.class.php";

session_start();

(isset($_GET['page']) && !empty($_GET['page']) && (int) $_GET['page'] > 0) ?
    $page = htmlspecialchars($_GET['page']) :
    $page = 1;

$customers = new PrintSQL('mysql', SERVER, DBB, USER, PASS);

$customers->setQuery("SELECT user_id AS code, first_name AS Prénom, role, email AS email, active, last_update AS MAJ
                    FROM customer
                    INNER JOIN user ON user.user_id = customer_id");

$customers->setLimit(10);
$customers->setOffset($page);

?>

<!DOCTYPE html>
<html lang="fr-FR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers</title>

    <!-- Scripts Semantic-UI et jQuery -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>
    <link rel="stylesheet" href="css/customers.css">
</head>

<body>

    <div class="ui grid container">
        <div class="row">
            <div class="column">
                <h2 class="ui dividing middle centered header">Tableau des utilisateurs</h2>
            </div>
        </div>

        <?php

        if (isset($_SESSION['edit']) && !empty($_SESSION['edit'])) {
            switch ($_SESSION['edit']) {
                case 'echec':
                    $col = 'warning';
                    $header = 'Une erreur s\'est produite.';
                    (isset($_SESSION['edit_msg']) && !empty($_SESSION['edit_msg'])) ?
                        $msg = $_SESSION['edit_msg'] :
                        $msg = "La modification a échoué. Veuillez réessayer.";
                    break;
                case 'succes':
                    $col = 'success';
                    $header = 'Modifications effectuées avec succès';
                    (isset($_SESSION['edit_msg']) && !empty($_SESSION['edit_msg'])) ?
                        $msg = $_SESSION['edit_msg'] :
                        $msg = "La modification a reussi.";
                    break;
            }
            include_once "inc/alert.edit.php";
        }

        if (isset($_SESSION['delete']) && !empty($_SESSION['delete'])) {
            switch ($_SESSION['delete']) {
                case 'echec':
                    $col = 'warning';
                    $header = 'Une erreur s\'est produite.';
                    (isset($_SESSION['delete_msg']) && !empty($_SESSION['delete_msg'])) ?
                        $msg = $_SESSION['delete_msg'] :
                        $msg = "La suppression a échoué. Veuillez réessayer.";
                    break;
                case 'succes':
                    $col = 'success';
                    $header = 'Suppression effectuée avec succès';
                    (isset($_SESSION['delete_msg']) && !empty($_SESSION['delete_msg'])) ?
                        $msg = $_SESSION['delete_msg'] :
                        $msg = "La suppression a reussi.";
                    break;
            }
            include_once "inc/alert.edit.php";
        }

        if (isset($_SESSION['create']) && !empty($_SESSION['create'])) {
            switch ($_SESSION['create']) {
                case 'echec':
                    $col = 'warning';
                    $header = 'Une erreur s\'est produite.';
                    (isset($_SESSION['create_msg']) && !empty($_SESSION['create_msg'])) ?
                        $msg = $_SESSION['create_msg'] :
                        $msg = "La création a échoué. Veuillez réessayer.";
                    break;
                case 'succes':
                    $col = 'success';
                    $header = 'Création effectuée avec succès';
                    (isset($_SESSION['create_msg']) && !empty($_SESSION['create_msg'])) ?
                        $msg = $_SESSION['create_msg'] :
                        $msg = "La création a reussi.";
                    break;
            }
            include_once "inc/alert.edit.php";
        }
        ?>

        <div class="row">
            <div class="column">
                <?php echo $customers->printTab(true); ?>
            </div>
        </div>

    </div>

    <?php

    include_once 'inc/customers.edit.modal.php';
    include_once 'inc/customers.delete.modal.php';
    include_once 'inc/customers.create.modal.php';

    unset($_SESSION['edit']);
    unset($_SESSION['edit_msg']);
    unset($_SESSION['delete']);
    unset($_SESSION['delete_msg']);
    unset($_SESSION['create']);
    unset($_SESSION['create_msg']);


    ?>

    <script src="js/customers.js"></script>
    <script src="js/customers.edit.js"></script>
    <script src="js/customers.delete.js"></script>
    <script src="js/customers.create.js"></script>
</body>

</html>