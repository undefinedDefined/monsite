<?php

include_once "class/form.class.php";
include_once "inc/globals.php";

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
    <?php

    $form = new Form('mysql', SERVER, DBB, USER, PASS);
    // $form->setTab('film_actor');
    // var_dump($form->is_fk('original_language_id'));
    echo $form->printForm("select * from customer");

    ?>

</body>