<?php

namespace Statut;

/**
 * Classe appelée pour afficher les pages rélatives aux statuts
 */

class View {
    /**
     * Affiche la page permettant de gérer les permissions des différents statuts.
     * @access public
     * @param Base $f3
     * @return void
     */
    public function permissions($f3) {
        if(CIA(EDIT_PERMISSIONS)) {
            afficherPage('templates/admin/permissions.htm');
        }
        else
            afficherPage (PAGE_ERREUR);
    }
}