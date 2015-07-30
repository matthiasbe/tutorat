<?php

namespace Alerte;

/**
 * 
 */

class Manager extends \Modele\Manager {
    protected function init() {
        $this->nature = 'Alerte';
        $this->table = 'alertes';
    }
}