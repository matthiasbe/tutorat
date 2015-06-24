<?php

/* 
 * Fonctions globales utilisées partout
 * Ce fichier et chargé en début de toute les pages
 */

/* Renvoie le nieme bit du nombre passé en paramètre */
function getBit($nombre, $n) {
    return (pow(2, $n) & $nombre)/pow(2, $n);
}

/* Renvoie TRUE si l'utilisateur loggé peut accéder à la page $page
    $page est le nom du template à accéder (ex: 'mon_template.htm') */
function verifierPermission($f3, $page) {
    /* si utilisateur connecté, on vérifie qu'il a bien les droits */
    if($f3->exists('SESSION.user')) {

        $id_status = $f3->get('SESSION.user')->getMembreFromId($f3)['statut'];
        /* On verifie que le status à été configurer (ou au moins un autre portant le même id */
        if($f3->exists('statuts.' . $id_status)) {
            $status = $f3->get('statuts.' . $id_status);
            if($f3->exists($status . '.' . $page)) {
                return $f3->get($status . '.' . $page);
            }
        }
    }
    /* Si pas de connexion, on regarde l'autorisation des invités */
    elseif($f3->exists('invite.' . $page) AND $f3->get('invite.' . $page))
        return 1;
    else
        return 0;
}

function afficherPage($f3, $page) {
        /* si utilisateur connecté, on vérifie qu'il a bien les droits */
    if(verifierPermission($f3, $page)) {
        $f3->set('page', $page);
        echo Template::instance()->render('templates/cadre.htm');
    }
    else {
        /* Sinon on renvoie la page d'erreur */
        $f3->set('page', 'templates/forbidden.htm');
        echo Template::instance()->render('templates/cadre.htm');
    }
}

// Tronque et rajoute 3 point si la chaîne est de taille > taille_max
function abreger($chaine, $taille_max) {
    if(strlen($chaine) > $taille_max) {
        if($taille_max >= 3) {
            return substr($chaine, 0, $taille_max - 3) . '...';
        }
        else
            return substr($chaine, $taille_max);
    }
    else return $chaine;
}

function test() {
    echo '<h2>TEST</h2>';
    echo ImgMng::getInstance()->estUneImage('files/images/edit.png');
}