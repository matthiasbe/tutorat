<?php

namespace Modele;

/**
 * Classe qui modélise les enregistrements des statuts dans la BDD.
 */

abstract class Data {
    /**
     * L'id auto-incrémenté.
     * @acces private
     * @var int
     */
    protected $id;
    
    /**
     * Le dossier contenant la classe fille : Résultat, Question etc...
     * @var string
     */
    protected $nature;
    
    public function __construct($donnees) {
        if(!is_array($donnees)) {
            trigger_error('Pour créer une classe \\' . $this->nature . '\\Data, un array doit être passé en paramètre.');
        }
        $this->init();
        $this->hydrate($donnees);
    }
    
    abstract protected function init();

    /**
     * Hydrate la classe avec le tableau passée en paramètre.
     * @access public
     * @param Array $donnees
     * @return void
     */
    public function hydrate($donnees) {
        foreach($donnees as $cle=>$valeur) {
            $setter = 'set' . ucfirst($cle);
            if(method_exists('\\' . $this->nature . '\\Data', $setter)) {
                $this->$setter($valeur);
            }
        }
    }
    
    /**
     * Renvoie l'id de d'objet courant.
     * @access public
     * @return int L'id de l'objet courant.
     */
    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        if(is_numeric($id) OR $id == "") {
            $this->id = $id;
        }
        else
            throw new Exception('Id invalide : ' . $id);
    }
    
    public function remplirMapper(\DB\SQL\Mapper $mapper) {
        foreach ($this as $key=>$value) {
            $mapper->$key = $value;
        }
    }
}