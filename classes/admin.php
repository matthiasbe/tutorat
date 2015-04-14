<?php

class Admin {
    
    /*
     *  Partie VUE
     */
    
    public function afficherListeMembres ($f3) {
        $f3->set('membres', $this->getMembres($f3));
        afficherPage($f3, 'templates/admin/membres/liste.htm');
    }
    
    public function afficherAjouterMembres ($f3) {
        afficherPage($f3, 'templates/admin/membres/ajouter.htm');
    }
    
    public function afficherSupprimerMembre ($f3) {
        afficherPage($f3, 'templates/admin/membres/supprimer.htm');
    }
    
    public function afficherPermissions ($f3) {
        afficherPage($f3, 'templates/admin/permissions.htm');
    }
    
    /*
     * Partie CONTROLLEUR
     */
    
    public function receptionAjouterMembre($f3) {
        $membre = new Membre(0);
        $membre->sauvegarderMembre($f3);
        $f3->reroute('/admin/membres/ajouter');
    }
    
    
    /*
     * Partie MODELE
     */
    
    public function getMembres($f3) {
        $db = $f3->get('Bdd');
        return $db->exec('SELECT * FROM membres');
    }
}