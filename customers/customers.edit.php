<?php

include_once "../class/model.class.php";
include_once "../inc/globals.php";

session_start();

$post = $_POST;

// On découpe les données reçues en fonction des tables dans lesquelles faire les update
if (isset($_POST) && !empty($_POST)) {
    $id = $post['id'];
    unset($post['id']);

    $role['role'] = $post['role'];
    unset($post['role']);
} else {
    return 'Aucunes données reçues';
    exit();
}


// On instancie notre classe
$dbh = new Model('mysql', SERVER, DBB, USER, PASS);

// Update les informations dans la table customer
$dbh->setTab('customer');
$edit_customer = $dbh->update($post, $id);

// Update role dans la table user
$dbh->setTab('user');
$edit_user = $dbh->update($role, $id);

// Si les deux update on réussi on renvoi un message de succès

if ($edit_customer && $edit_user) {
    $_SESSION['edit'] = "succes";
    $_SESSION['edit_msg'] = "La modification de l'utilisateur $id a réussi.";
} else {
    $_SESSION['edit'] = "echec";
    $_SESSION['edit_msg'] = "La modification de l'utilisateur $id a échouée. Veuillez réessayer.";
}


header('location: index.php');
