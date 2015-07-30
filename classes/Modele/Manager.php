<?php

namespace Modele;


/**
 * 
 */

abstract class Manager extends \Prefab {
    
    /**
     * @var \DB\SQL
     */
    protected $db;
    
    /**
     * Le dossier contenant la classe fille : Résultat, Question etc...
     * @var string
     */
    protected $nature;
    
    private $data_class;
    
    /**
     * Le nom de la table relative à la classe data.
     * @var string
     */
    protected $table;
    
    protected function __construct() {
        $this->init();
        $this->db = \Base::instance()->get('Bdd');
        $this->data_class = '\\' . $this->nature . '\\Data';
    }
    
    /**
     * Doit instancer les variable table et nature.
     */
    protected abstract function init();

    /**
     * Enregistre un nouvel objet dans la base de données.
     * @access public
     * @param Data $objet L'objet à ajouter.
     * @return
     */
    public  function add($objet) {
        if($this->idExiste($objet->getId())) throw new \Exception('Fonction ADD sur un ' . $this->data_class . ' existant déjà dans la BDD.');
        
        $objet_db = new \DB\SQL\Mapper($this->db, $this->table);
        $objet->remplirMapper($objet_db);
        $objet_db->save();
    }
    
    /**
     * Met à jour un objet dans la BDD
     * @access public
     * @param Data $objet
     * @return void
     */
    public function update($objet) {
        if($this->idExiste($objet->getId())) {
            $objet_db = new \DB\SQL\Mapper($this->db, $this->table);
            $objet_db->load(array('id=?', $objet->getId()));
            $objet->remplirMapper($objet_db);
            $objet_db->save();
        }
        else {
            trigger_error('Le statut a un ID invalide.');
        }
    }

    /**
     * Suppression d'un objet de la BDD. Renvoie une erreur si il à une ID inexistante.
     * @access public
     * @param Data $objet le membre à mettre à jour
     * @return void
     */
    public  function delete($objet) {
        if($this->idExiste($objet->getId())) {
            $objet_db = new \DB\SQL\Mapper($this->db, $this->table);
            $objet_db->erase(array('id=?', $objet->getId()));
        }
        else {
            throw new\Exception('Impossible de supprimer ce membre : ID inconnu. id : ' . $objet->getId());
        }
    }
    
    /**
     * Récupère un statut dans la bdd à partir de son ID.
     * @param int $id_objet
     * @return Data Le statut ayant pour id $id_statut, NULL sinon.
     */
    
    public function getFromId($id_objet) {
        if($this->idExiste($id_objet)) {
            $res_array = $this->db->exec('SELECT * FROM ' . $this->table . ' WHERE id=?', $id_objet)[0];
            return new $this->data_class($res_array);
        }
        else {
            return $this->getInvite();
        }
    }
    
    /**
     * @access public
     * @return Array Tous les statut de la BDD.
     */
    public function getAll() {
        $objets_db = $this->db->exec('SELECT * FROM ' . $this->table);
        $objets = array();
        foreach($objets_db as $objet) {
            $objets[$objet['id']] = new $this->data_class($objet);
        }
        return $objets;
    }
    
    /**
     * Détermine si un objet existe ou non dans la BDD.
     * @access public
     * @param int $id
     * @return bool True si un objet avec l'id donné existe et est unique
     */
    public function idExiste($id) {
        $objet = new \DB\SQL\Mapper($this->db, $this->table);
        $nombre_objets = $objet->count(array('id=?', $id));
        return ($nombre_objets == 1);
    }
    
    /**
     * Permet d'ajouter une alerte pour tous les membres concernés.
     * @access protected
     * @param Data $objet L'objet qui fait l'objet de la notification.
     * @param bool $ajout True s'il s'agit d'une ajout. False pour une modification.
     * @return void
     */
    protected function notifier($objet, $ajout) {
        $type = $ajout ? \Alerte\Data::ALERT_TYPE_CREATION : \Alerte\Data::ALERT_TYPE_MODIF;
        $alerte = new Alerte(array(
            'contenu_id' => $objet->getId(),
            'contenu_classe' => $this->nature,
            'type' => $type,
//            'membres' => $membres
        ));
        \Alerte\Manager::instance()->add($alerte);
    }
    
    /**
     * transforme un tableau de résultats en un tableau d'objets
     * @access private
     * @param array $mappers
     * @return Data
     */
    protected function results2objects($mappers) {
        // On renvoie les résultats en transformant les mapper en structure de question
        $objets = array();
        foreach($mappers as $key=>$objet) {
            $objets[$key] = new $this->data_class($objet);
        }
        return $objets;
    }
}