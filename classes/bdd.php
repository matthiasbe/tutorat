<?php

class Bdd {
    private $host;
    private $username;
    private $password;
    private $database;
    private $port;
    
    private $db = 0;
    
    function __construct($f3) {
        if($this->db == 0) {
            $this->initFromConfig($f3);
            
            $this->db = new DB\SQL(
                'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->database,
                $this->username,
                $this->password
            );
        }
    }
    
    public function initFromConfig($f3) {
        $this->host = $f3->get('bdd.host');
        $this->username = $f3->get('bdd.username');
        $this->password = $f3->get('bdd.password');
        $this->database = $f3->get('bdd.database');
        $this->port = $f3->get('bdd.port');
    }
    
    public function getDb() {
        return $this->db;
    }
}