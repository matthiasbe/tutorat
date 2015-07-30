<?php

namespace Alerte;

/**
 * 
 */

class Data extends \Modele\Data {
    const ALERT_TYPE_MODIF = 1;
    const ALERT_TYPE_CREATION = 2;
    
    public function getNature() {
        return $this->nature;
    }
    
    protected function init() {
        $this->nature = 'Alerte';
    }
    
    
    /**
     * Le de l'alerte. Voir constantes de la classe ALERT_TYPE_XXXX. Modification ou création.
     * Modificaton : il s'agit de la modification d'un contenu déjà existant.
     * Création : on crée du nouveau contenu.
     * @var int
     */
    private $type;
    
    /**
     * L'id du contenu qui à été modifier.
     * @var int
     */
    private $contenu_id;
    /**
     * La classe du contenu, vaut Data de Membre, Sujet, Question ou Resultat.
     * @var string.
     */
    private $contenu_classe;
    
    /**
     * Array contenant en index les id des membres visés par la notification et en valeur
     * un bouléen qui vaut true si il n'ont pas encore vu la notif, false sinon.
     * @var array
     */
    private $membres = array();
    
    public function getType() {
        return $this->type;
    }
    
    public function setType($type) {
        if(is_numeric($type)) {
            $this->type = $type;
        }
        else
            throw new Exception ('Type invalide : ' . $id);
    }
    
    public function getContenu() {
        $class = '\\' . $this->contenu_classe . '\\Manager';
        $manager = $class::instance();
        if(!method_exists($class, 'getFromId')) throw new \Exception('La classe n\'a pas de méthode getFromId()');
        return $manager->getFromId($this->contenu_id);
    }
    
    public function setContenu_id($contenu_id) {
        if(is_numeric($contenu_id)) {
            $this->contenu_id = $contenu_id;
        }
        else
            throw new Exception ('Id de contenu invalide : ' . $contenu_id);
    }
    
    public function setContenu_nature($nature) {
        if(class_exists($nature)) {
            $this->contenu_classe = $nature;
        }
        else
            throw new Exception ('Nature de contenu invalide, classe inconnue : ' . $nature);
    }
    
    /**
     * Retourne un tableau de booléens indiquant si la notif à été lue.
     * @access public
     * @return Array un tableau contenant tous les membres visés en indices.
     */
    public function getMembres() {
        return $this->membres;
    }
    
    /**
     * Ajoute un membre visé par la notif. Ce membre est noté comme ne l'ayant pas encore lu.
     * @access public
     * @param \Membre\Data $membre Le membre à ajouter
     * @return void
     */
    public function addMembre(\Membre\Data $membre) {
        $this->membres[$membre->getId()] = 1;
    }
    
    /**
     * Retire un membre de la notif
     * @access public
     * @param \Membre\Data $membre
     * @throws \Exception Si le membre passé en paramètre n'est pas répertorié dans la notif.
     * @return void
     */
    public function removeMembre(\Membre\Data $membre) {
        if(!in_array($membre->getId(), $this->membres)) throw new \Exception('Le membre ne fait pas partie des membres visés par la notification.');
        $array_index = array_flip($this->membres)[$membre->getId()];
        
        unset($this->membres[$array_index]);
    }
    
    /**
     * Permet de mettre une liste de membre serializés comme bénéficiaires de la notif.
     * @access public
     * @param mixed $membres Un tableau qui peut être serializé.
     * @return void
     */
    public function setMembres($membres) {
        if(is_array($membres)) {
            $this->membres = $membres;
        }
        elseif(is_string($membres)) {
            $this->membres = unserialize($membres);
        }
    }
    
    public function membreALu(\Membre\Data $membre) {
        if(!in_array($membre->getId(), $this->membres)) throw new \Exception('Le membre ne fait pas partie des membres visés par la notification.');
        $this->membres[$membre->getId()] = 0;
    }
    
    public function membreAPasLu(\Membre\Data $membre) {
        if(!in_array($membre->getId(), $this->membres)) throw new \Exception('Le membre ne fait pas partie des membres visés par la notification.');
        $this->membres[$membre->getId()] = 1;
    }
}