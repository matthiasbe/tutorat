<?php
phpinfo();
// Définition des constantes
include('liste_permissions.php');
define('PAGE_ERREUR', 'templates/forbidden.htm');

// Ajout des librairies
$f3 = require('lib/base.php');
//Ajout des fichier de configuration
$f3->config('config/config.ini');
$f3->config('config/routes.ini');
$f3->config('config/bdd.ini');

// Définition d'autres constante dans le Hive (ensemble des variable stockée pendant le chargement) de Fatfree Framework
$bdd = new Bdd($f3);
$f3->set('Bdd', $bdd->getDb());
$f3->set('f3', $f3);
$f3->set('FILE', __FILE__);

/* Fonctions globales qu'on utilise très souvent */
include('fct_globales.php');

// On recharge la classe de la personne connectée. (si il y a eu des changement dessus depuis le dernier chargement)
\Membre\Manager::instance()->refreshConnection();

// On lance l'application
$f3->run();
