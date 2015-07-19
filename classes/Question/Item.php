<?php

namespace Question;

/*
 * Classe contenant les données d'un item
 */

class Item {
    protected $item;
    protected $correction;
    
    public function __construct() {
        $this->setItem('');
        $this->setCorrection('');
    }
    
    public function setItem($item) {
        $this->item = $item;
    }
    
    public function setCorrection($correction) {
        $this->correction = $correction;
    }
    
    public function getItem() {
        return $this->item;
    }
    
    public function getCorrection() {
        return $this->correction;
    }
}