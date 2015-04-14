<?php

class Main {
    
    public function AfficherAccueil($f3) {
        $f3->set('root', '');
        afficherPage($f3, 'templates/accueil.htm');
    }
}