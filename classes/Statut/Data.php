<?php

namespace Statut;

/**
 * Classe qui modélise les enregistrements des statuts dans la BDD.
 */

class Data extends \Modele\Data {
    protected function init() {
        $this->nature = 'Statut';
    }
    
    /**
     * Le nom du statut.
     * @access private
     * @var string
     */
    private $nom;
    
    /**
     * Un entier sur 64 bits contenant les différentes permissions d'un statut.
     * @access private
     * @var bigint
     */
    private $permissions;
    
    /**
     * Renvoie le nom du statut courant.
     * @access public
     * @return int Le nom du statut courant.
     */
    public function getNom() {
        return $this->nom;
    }
    
    public function setNom($nom) {
        $this->nom = $nom;
    }
    
    /**
     * @access public
     * @return bigint
     */
    public function getPermissions() {
        return $this->permissions;
    }
    
    public function setPermissions($perms) {
        $this->permissions = $perms;
    }
    
    /**
     * Indique si la permission existe pour le statut courant.
     * @access public
     * @param int $perm Numéro de la permission
     * @return bool
     */
    public function getPermission($perm) {
        return getBit($this->permissions, $perm);
    }
    
    /**
     * Change une permission pour le statut courant.
     * @access public
     * @param int $perm Le numéro de la permission.
     * @param int $value La nouvelle valeur de la permission.
     * @return bool
     */
    public function setPermission($perm, $value) {
        $this->permissions = setBit($this->permissions, $perm, $value);
    }
}