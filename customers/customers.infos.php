<?php

include_once "../class/connexion.class.php";
include_once "../inc/globals.php";

$dbh = new Connexion();
$dbh->setConfig('mysql', SERVER, DBB, USER, PASS);
$dbh->connect();

if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id = htmlspecialchars($_POST['id']);
} else {
    echo 'Une erreur est survenue';
    exit();
}

$query = "SELECT c.first_name prenom, c.last_name nom, c.email, CONCAT(a.address, ' ', a.district, ' (', country, ')') adresse, role, active, a.address_id, c.customer_id
FROM user u
    INNER JOIN customer c ON c.customer_id = user_id
    INNER JOIN address  a ON a.address_id = c.address_id
    INNER JOIN city ON city.city_id = a.city_id
    INNER JOIN country ON country.country_id = city.country_id
WHERE customer_id = ?";

$param = array($id);
$res = $dbh->getData($query, $param);

?>


<form action="customers.edit.php" method="post" class="ui edit user form container" id="edit_form">
    <div class="three fields">
        <div class="seven wide field">
            <label>Prénom</label>
            <input type="text" name="first_name" value="<?= $res[0]['prenom']  ?>">
        </div>
        <div class="seven wide field">
            <label>Nom</label>
            <input type="text" name="last_name" value="<?= $res[0]['nom']  ?>">
        </div>
        <div class="three wide field">
            <label>Role</label>
            <select name="role" class="ui search dropdown">
                <?php for ($i = 0; $i <= 5; $i++) {
                    echo '<option ' . ($res[0]['role'] == $i ? 'selected' : '') . ' value="' . $i . '">' . $i . '</option>';
                } ?>
            </select>
        </div>
    </div>
    <div class="two fields">
        <div class="fourteen wide field">
            <label>Email</label>
            <input type="text" name="email" value="<?= $res[0]['email']  ?>">
        </div>
        <div class="two wide field">
            <label>Active</label>
            <select name="active" class="ui search dropdown">
                <?php for ($i = 0; $i < 2; $i++) {
                    echo '<option ' . ($res[0]['active'] == $i ? 'selected' : '') . ' value="' . $i . '">' . $i . '</option>';
                } 
                ?>
            </select>
        </div>
    </div>
    <div class="field">
        <input id="id" name="id" type="hidden" value="<?= $res[0]['customer_id']  ?>">
    </div>
    <div class="field">
        <label>Adresse</label>
        <select name="address_id" class="ui search dropdown">
            <option value="<?= $res[0]['address_id']  ?>"><?= $res[0]['adresse']  ?></option>
            <?php
            $query = "SELECT a.address_id, CONCAT(a.address, ' ', a.district, ' (', country, ')') adresse 
                FROM address a
                INNER JOIN city ON city.city_id = a.city_id
                INNER JOIN country ON country.country_id = city.country_id";
            foreach ($dbh->getData($query) as $row) {
                echo '<option ' . ($row['address_id'] == $res[0]['address_id'] ? 'selected' : '') . ' value="' . $row['address_id'] . '">' . $row['adresse'] . '</option>';
            } 
            
            ?>

        </select>
    </div>
    <!-- <div class="ui error message"></div> -->
</form>