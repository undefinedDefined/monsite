<?php

include_once "../class/model.class.php";
include_once "../inc/globals.php";

session_start();

$post = $_POST;

if (isset($post['id']) && !empty($post['id'])) {
    $id = $post['id'];
} else {
    return 'Aucunes données reçues';
    exit();
}

// var_dump($post);
// exit();

// On instancie notre classe
$dbh = new Model('mysql', SERVER, DBB, USER, PASS);

// On supprimer dans la table user
$dbh->setTab('user');
$delete_user = $dbh->delete($id);

// On supprimer dans la table customer
$dbh->setTab('customer');
$delete_customer = $dbh->delete($id);

if ($delete_customer && $delete_user) {
    $_SESSION['delete'] = "succes";
    $_SESSION['delete_msg'] = "La modification de l'utilisateur $id a réussi.";
} else {
    $_SESSION['delete'] = "echec";
    $_SESSION['delete_msg'] = "La modification de l'utilisateur $id a échouée. Veuillez réessayer.";
}

header('location: index.php');