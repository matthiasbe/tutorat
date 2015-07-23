<?php
define('MAIL_VALIDATION_SUJET', 'Vos identifiant pour le site du tutorat.');
define('ERREUR_MATIERE', 'Vous n\'avez aucune matière attribuée à votre profil. Contactez l\'administrateur.');

/**
 * Fonctions globales utilisées partout
 * Ce fichier et chargé en début de chaque pages
 */

/**
 * Renvoie le nieme bit du nombre passé en paramètre
 * @param int $nombre
 * @param int $n Le numéro du bit à renvoyé. En commençant par 1 le bit de poid faible (à droite).
 * @return bool
 */
function getBit($nombre, $n) {
    return floor($nombre/pow(2,$n-1))%2;
}

/**
 * Renvoie le nombre binaire transformé tel que son nieme bit soit à la valeur $valeur.
 * @access public
 * @param int $nombre Le nombre à modifier.
 * @param int $n Le numéro du bit à modifier (commence à 1).
 * @param bool $value La nouvelle valeur du bit.
 * @return int Le nombre transformé.
 */
function setBit($nombre, $n, $value) {
    if(getBit($nombre, $n) != $value) {
        if($value) {
            return $nombre + pow(2, $n-1);
        }
        else {
            return $nombre - pow(2, $n-1);
        }
    }
}

function bitSum($n) {
    $somme = 0;
    while($n != 0) {
        if(is_int($n/2)) { // Si n est impair
            $n--;
            $somme++;
        }
        $n/2; // On décale à droite
        echo 'n : ' . $n . '<br/>';
    }
    return $somme;
}

/**
 * Affiche le template demandé dans le template cadre.htm si la permission est accordé.
 * Sinon affiche une page d'erreur dans le template cadre.htm.
 * @param string $page La page que l'on souhaite afficher.
 * @param int $permission La permission nécessaire pour afficher la page.
 * @param bool $condition Un condition supplémentaire à l'affichage de la page.
 * @return void
 */
function afficherPage($page) {
    $f3 = Base::instance();
    $f3->set('page', $page);
    echo Template::instance()->render('templates/cadre.htm');
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

/**
 * Abréviation de ConnectedIsAllowed.
 * Vérifie si l'étudiant connecté à la permission donnée.
 * @param int $perm La permission à vérifier.
 * @param bool $invite True si un invité peut accéder à la page
 * @param mixed $question_ou_sujet Si la personne connecté en est l'auteur, il peut accéder à la page.
 * @return bool True si l'étudiant connecté est autorisé.
 */
function CIA($perm, $invite = 1, $question_ou_sujet = NULL) {
    $etudiant_connecte = \Membre\Manager::instance()->getConnected();
    if($etudiant_connecte) {
        return ($etudiant_connecte->getStatutObject()->getPermissions() & $perm) ||
               ($question_ou_sujet && $etudiant_connecte->isAuteur($question_ou_sujet));
    }
    else {
        return $invite && (\Statut\Manager::instance()->getInvite()->getPermissions() & $perm);
    }
}

/**
 * Détermine si personne connecté à la possibilité d'accéder à un endroit réserver aux webmaster en cours de travail.
 * @access public
 * @return bool True si la personne est autorisée.
 */
function vip() {
    return \Membre\Manager::instance()->getConnected()->getId() == 1;
}