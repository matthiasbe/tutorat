<?php

namespace Modele;

/**
 * Classe abstraite permettant de modéliser une donnée récupérée depuis une certaine table de la base de donnée.
 * Pour l'utiliser :
 *  * ajouter les champs de la table en protected
 */

abstract class Data {
    /**
     * L'id auto-incrémenté.
     * @acces private
     * @var int
     */
    protected $id;
    
    /**
     * Permet de connaitre le Namespacede la classe fille (ex : Membre, Sujet)
     * @return string Le namespace dans lequel on se trouve.
     */
    protected function getNamespace() {
        return explode('\\', get_class($this))[0];
    }
    
    /**
     * Construction de la classe.
     * @param array $donnees Un tableau contenant les données permettant de remplir la classe.
     */
    public function __construct($donnees) {
        if(!is_array($donnees)) {
            trigger_error('Pour créer une classe \\' . $this->getNamespace() . '\\Data, un array doit être passé en paramètre.');
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
            $setter = 'set' . ucfirst($cle);
            if(method_exists('\\' . $this->getNamespace() . '\\Data', $setter)) {
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
    
    /**
     * Met à jour le paramètre id.
     * @param int $id L'identifiant de la donnée. Unique dans la table (clef primaire).
     * @throws Exception
     */
    public function setId($id) {
        if(is_numeric($id) OR $id == "") {
            $this->id = $id;
        }
        else
            throw new Exception('Id invalide : ' . $id);
    }
    
    /**
     * Remplit un mapper SQL fournit par FatFree à partir des propriétés de la classe.
     * @param \DB\SQL\Mapper $mapper Le mapper à remplir.
     */
    public function remplirMapper(\DB\SQL\Mapper $mapper) {
        foreach ($this as $key=>$value) {
            $mapper->$key = $value;
        }
    }
}