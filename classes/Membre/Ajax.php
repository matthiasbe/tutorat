<?php

namespace Membre;

/**
 * Reçoit les requètes ajax
 */

class Ajax {
    
    /**
     * Modifie un champ concernant un membre.
     * La permission de modifier est vérifiée.
     * POST.id : id du membre dont on modifie une caractéristique.
     * POST.champ : nom du champ à modifier.
     * POST.valeur : nouvelle valeur du champ
     * @access public
     * @param Base $f3
     * @return void
     */
    public function modifierChamp($f3) {
        $id_membre = $f3->get('POST.id');
        $champ = $f3->get('POST.champ');
        $valeur = $f3->get('POST.valeur');
        if(Manager::instance()->estAutoriseAModifier($id_membre, $champ)) {
            $m_manager = Manager::instance();
            // On récupère le membre
            $membre = $m_manager->getFromId($id_membre);
            // On le modifie
            $setter = 'set' . ucfirst($champ); // La fonction permettant de modifier le champ dans la classe Data
            $membre->$setter($valeur);
            // On l'enregistre
            $m_manager->update($membre);
        }
        else {
            echo ERREUR;
        }
    }
    
    /**
     * Réceptionne la requète Ajax de suppression de membre.
     * POST.id : id du membre à supprimer.
     * @access public
     * @param Base $f3
     * @return void
     */
    public function supprimerMembre($f3) {
        $m_manager = Manager::instance();
        if(CIA(DELETE_MEMBRE)) {
            $membre = $m_manager->getFromId($f3->get('POST.id'));
            if($membre != NULL) {
                $m_manager->delete($membre);
            }
            else {
                trigger_error('Impossible de supprimer: membre inconnu.');
            }
        }
        else {
            echo ERREUR;
        }
    }
}