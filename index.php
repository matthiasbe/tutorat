<?php

$f3=require('lib/base.php');
$f3->config('config/config.ini');
$f3->config('config/routes.ini');
$f3->config('config/bdd.ini');
$f3->config('config/permissions.ini');

$bdd = new Bdd($f3);
$f3->set('Bdd', $bdd->getDb());
$f3->set('f3', $f3);
$f3->set('FILE', __FILE__);

/* Fonctions globales qu'on utilise très souvent */
include('fct_globales.php');


$f3->run();