<?php
define("ID_INVITE", 50);
define("ERREUR", 'Vous n\'avez pas les autorisations nécessaires pour effectuer cette action.');

define("NBR_PERMISSIONS", 32);
/*************************
 * LISTE DES PERMISSIONS *
 *************************/

// Permissions concernant la modification du profil
define("EDIT_OTHER_PSEUDO",     0x1);
define("EDIT_OTHER_NOM",        0x2);
define("EDIT_OTHER_PRENOM",     0x4);
define("EDIT_OTHER_SITUATION",  0x8);
define("EDIT_OTHER_SITE",       0x10);
define("EDIT_OWN_EMAIL",        0x20);
define("EDIT_OTHER_EMAIL",      0x40);
define("EDIT_OWN_MDP",          0x80);
define("EDIT_OTHER_MDP",        0x100);
define("EDIT_OWN_PORTABLE",     0x200);
define("EDIT_OTHER_PORTABLE",   0x400);
define("EDIT_OTHER_MATIERES",   0x800);
define("EDIT_OTHER_STATUT",     0x1000);

/* Permissions concernant l'affichage des différentes pages
 * Il s'agit ici uniquement des éléments non propres à l'étudiant connecté:
 * Un étudiant peut voir son propre profil, voir ses résultats, voir ses questions fausses.
 * 
 * De même un tuteur ayant créér un sujet/une question peut le/la modifier et le/la supprimer.
 * Dans la cas d'une question, il en perd le droit si elle est rattachée à un sujet.
 * 
 * Tous les étudiants peuvent accéder à la page des QCMS et des sujets/corrigés
 * Tout le monde peut accéder à la page d'accueil
 */

define("DELETE_MEMBRE",     0x2000); // Desinscrire un profil d'étudiant (il ne pourra plus accéder à son compte)
define('SEE_QCMS',          0x4000); // Accéder à la banque de QCM
define('SEE_SUJETS_ET_COR', 0x8000); // Voir les sujets et leurs corrections au format pdf.
define('ADD_MEMBRE',        0x10000); // Enregistrer des comptes étudiants
define("DELETE_SUJET",      0x20000); // Supprimer le sujet d'un autre tuteur
define("SEE_MEMBRES",       0x40000); // Voir la liste des étudiants
define("SEE_PROFILS",       0x80000); // Voir les profils des autres étudiants
define("EDIT_PERMISSIONS",  0x100000); // Accéder à la page des permissions et les modifier
define("UPLOAD_RESULTS",    0x200000); // Pouvoir uploader des résultats
define("SEE_RESULTS",       0x400000); // Voir les résultats des autres étudiants
define("ADD_SUJET",         0x800000); // Ajouter un nouveau sujet (dans sa propre matière)
define("ADD_QUESTION",      0x1000000); // Ajouter une nouvelle question (dans sa propre matière)
define("EDIT_SUJET",        0x2000000); // Editer le sujet d'un autre tuteur (dans sa propre matière)
define("EDIT_QUESTION",     0x4000000); // Editer la question d'un autre tuteur (dans sa propre matière)
define("SEE_QUESTIONS",     0x8000000); // Voir les questions des autres tuteurs (dans sa propre matière)
define("SEE_SUJETS",        0x10000000); // Voir les sujets des autres tuteurs (dans sa propre matière)
define("BANK",              0x20000000); // Banquer/débanque les question de sa matière
define("ATTACH",            0x40000000); // Attacher/détacher une question à un/d'un sujet
define("DELETE_QUESTION",   0x40000000); // Supprimer la question d'une autre tuteur
define("SEE_CARNETS",       0x80000000); // Voir la page des carnets de cours
// -------- On passe la barre des 32 bits. A partir d'ici, on doit utiliser un bigint sur 64 bits
// -> impossible sur le serveur OVH

define("ADD_CARNET", ADD_SUJET);
define("DELETE_CARNET", DELETE_SUJET);