<?php

include_once "../class/model.class.php";
include_once "../inc/globals.php";

session_start();

$customer = $_POST;

// On découpe les données reçues en fonction des tables dans lesquelles faire les update
if (isset($_POST) && !empty($_POST)) {

    $user['role'] = $customer['role'];
    (isset($customer['email']) && isset($customer['password'])) ?
    $user['password'] = hash('sha256',(hash('md5', $customer['password']).hash('sha1', strtolower($customer['email'])))) :
    '';
    
    unset($customer['role']);
    unset($customer['password']);
    unset($customer['confirm_password']);

} else {
    return 'Aucunes données reçues';
    exit();
}

// On instancie notre classe
$dbh = new Model('mysql', SERVER, DBB, USER, PASS);

// On vérifie que l'adresse email n'existe pas
if(empty( $dbh->getData("SELECT * FROM customer WHERE email = ?", array($customer['email']))) ){
    // Creation de l'utilisateur dans la table customer et récupération de son id
    $dbh->setTab('customer');
    $user['user_id'] = $dbh->insert($customer, true);

    // Creation de l'utilisateur dans la table user
    $dbh->setTab('user');
    $create_user = $dbh->insert($user);    
}else{
    $_SESSION['create'] = "echec";
    $_SESSION['create_msg'] = 'La création de l\'utilisateur '.$customer['first_name']. " ".$customer['last_name'].' a échouée. L\'adresse email '.$customer['email'].' est déjà utilisée.';
}

// Envoi du message approprié via session
if ($create_user) {
    $_SESSION['create'] = "succes";
    $_SESSION['create_msg'] = 'La création de l\'utilisateur '.$customer['first_name']. " ".$customer['last_name'].' est complète';
} else {
    $_SESSION['create'] = "echec";
    $_SESSION['create_msg'] = 'La création de l\'utilisateur '.$customer['first_name']. " ".$customer['last_name'].' a échouée. Veuillez réessayer.';
}


header('location: index.php');
