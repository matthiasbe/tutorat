<?php

class General {
    
    public function AfficherAccueil($f3) {
        $f3->set('root', '');
        afficherPage('templates/accueil.htm');
    }
    
    public function AfficherCalendrier($f3) {
        $f3->set('sujets', \Sujet\Manager::instance()->getAVenir());
        afficherPage('templates/calendrier.htm');
    }
}