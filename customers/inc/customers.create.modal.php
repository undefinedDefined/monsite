<?php
include_once "../class/connexion.class.php";
include_once "../inc/globals.php";

$dbh = new Connexion();
$dbh->setConfig('mysql', SERVER, DBB, USER, PASS);
$dbh->connect();

$query = "SELECT a.address_id, CONCAT(a.address, ', ', a.district, ' (', country, ')') adresse 
FROM address a
INNER JOIN city ON city.city_id = a.city_id
INNER JOIN country ON country.country_id = city.country_id";

$query2 = "SELECT store_id, CONCAT(a.address, ', ', a.district, ' (', country, ')') adresse 
FROM store
INNER JOIN address a ON a.address_id = store.address_id
INNER JOIN city ON city.city_id = a.city_id
INNER JOIN country ON country.country_id = city.country_id"


?>


<div class="ui coupled edit user modal" id="create_modal">
    <i class="close icon"></i>
    <div class="header">
        Création d'utilisateur
    </div>
    <div class="content">
        <form action="customers.create.php" method="post" class="ui edit user form container" id="create_form">

            <div class="three fields">

                <div class="seven wide field">
                    <label>Prénom</label>
                    <input type="text" name="first_name">
                </div>
                <div class=" seven wide field">
                    <label>Nom</label>
                    <input type="text" name="last_name">
                </div>
                <div class="three wide field">
                    <label>Role</label>
                    <select name="role" class="ui search dropdown">
                        <?php $html = '';

                        $roles = 5;
                        for ($i = 0; $i <= $roles; $i++) {
                            echo '<option value="' . $i . '">' . $i . '</option>';
                        }

                        echo $html; ?>
                    </select>
                </div>

            </div>

            <div class="two fields">
                <div class="ten wide field">
                    <label>Adresse</label>
                    <select name="address_id" class="ui search dropdown">
                        <?php $html = '';

                        foreach ($dbh->getData($query) as $row) {
                            $html .= '<option value="' . $row['address_id'] . '">' . $row['adresse'] . '</option>';
                        }

                        echo $html; ?>
                    </select>
                </div>
                <div class="six wide field">
                    <label for="store_id">Magasin</label>
                    <select name="store_id" class="ui search dropdown">
                        <?php $html = '';

                        foreach ($dbh->getData($query2) as $row) {
                            $html .= '<option value="' . $row['store_id'] . '">' . $row['adresse'] . '</option>';
                        }

                        echo $html; ?>
                    </select>
                </div>
            </div>

            <div class="two fields">
                <div class="fourteen wide field">
                    <label>Email</label>
                    <input type="text" name="email"">
                </div>
                <div class=" two wide field">
                    <label>Active</label>
                    <select name="active" class="ui search dropdown">
                        <?php $html = '';

                        $activeStates = 2;
                        for ($i = 0; $i < $activeStates; $i++) {
                            $html .= '<option value="' . $i . '">' . $i . '</option>';
                        }

                        echo $html; ?>
                    </select>
                </div>
            </div>

            <div class="two fields">
                <div class="eight wide field">
                    <label for="password">Mot de passe</label>
                    <input type="password" name="password">
                </div>
                <div class="eight wide field">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" name="confirm_password">
                </div>
            </div>


            <div class="ui error message"></div>
        </form>
    </div>
    <div class="actions">
        <div class="ui clear button" style="float: left;">
            Effacer
        </div>
        <div class="ui close button">Annuler</div>
        <button class="ui primary right labeled icon submit button" id="create_submit">
            <i class="right arrow icon"></i>
            Ajouter
        </button>
    </div>
</div>


<div class="ui coupled mini confirm edit modal" id="create_confirm">
    <div class="header">AJout d'utilisateur</div>
    <div class="content">
        <p>Confirmez vous la création de cet utilisateur ?</p>
    </div>
    <div class="actions">
        <div class="ui negative cancel button">Annuler</div>
        <div class="ui positive approve button">Confirmer</div>
    </div>
</div>