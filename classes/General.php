<?php

class General {
    
    private static $f3;
    
    public static function getF3() {
        return self::$f3;
    }
    
    public static function setF3($f3) {
        self::$f3 = $f3;
    }
    
    public function AfficherAccueil($f3) {
        $f3->set('root', '');
        afficherPage($f3, 'templates/accueil.htm');
    }
}