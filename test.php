<?php

include_once "class/form.class.php";
include_once "inc/globals.php";

$form = new Form('mysql', SERVER, DBB, USER, PASS);
echo $form->printForm("SELECT * FROM customer");

