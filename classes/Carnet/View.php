<?php

namespace Carnet;

/* 
 * Classe appelée pour afficher les pages rélatives aux carnets
 */

class View {
    
    /**
     * Affiche la liste des carnets de cours disponibles, organisés par matières.
     * @param \Base $f3
     */
    public function liste($f3) {
        // Les droits d'ajout et de suppression sont vérifiés dans la fonction de la classe Manager
        try {
            Manager::instance()->addAndDeleteFromRequest();
        } catch (\Exception $e) {
            \Msg::instance()->add(\Msg::STATUT_ERROR, $e->getMessage());
        }

        
        if(CIA(SEE_CARNETS)) {
            $f3->set('carnets', Manager::instance()->getAll());
            afficherPage('templates/carnets/liste_carnets.htm');
        }
        else
            afficherPage (PAGE_ERREUR);
    }
}