<?php

namespace Statut;

/**
 * Classe qui modélise les enregistrements des statuts dans la BDD.
 */

class Data {
    /**
     * L'id du statut, auto-incrémenté.
     * @acces private
     * @var int
     */
    private $id;
    
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
     * Construit la classe à partir d'un array contenant des données pour l'hydrater.
     * @access public
     * @param array $donnees
     * @return void
     */
    public function __construct($donnees) {
        if(!is_array($donnees)) {
            trigger_error('Pour créer une classe Resultat\Data, un array doit être passé en paramètre.');
        }
        $this->hydrate($donnees);
    }

    /**
     * Hydrate la classe avec le tableau passée en paramètre.
     * @access public
     * @param Array $donnees
     * @return void
     */
    public function hydrate($donnees) {
        foreach($donnees as $cle=>$valeur) {
            $this->$cle = $valeur;
        }
    }
    
    /**
     * Renvoie l'id du statut courant.
     * @access public
     * @return int L'id du statut courant.
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Renvoie le nom du statut courant.
     * @access public
     * @return int Le nom du statut courant.
     */
    public function getNom() {
        return $this->nom;
    }
    
    /**
     * @access public
     * @return bigint
     */
    public function getPermissions() {
        return $this->permissions;
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
    
    /**
     * remplit un Mapper avec les champs du statut
     * @access public
     * @param \DB\SQL\Mapper $mapper
     * @return void
     */
    public function remplirMapper(\DB\SQL\Mapper $mapper) {
        foreach ($this as $key=>$value) {
            $mapper->$key = $value;
        }
    }
}