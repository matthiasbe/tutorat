<?php

namespace Sujet;

/**
 * Sujet Manager permet de gérer les sujets, notamment les échanges avec la base de données.
 */

class Manager {
    
    /**
     * @var \DB\SQL
     */
    private $db;
    
    /**
     * @var self
     */
    private static $instance;
    
    private function __construct() {
        $this->db = \Base::instance()->get('Bdd');
    }

    /**
     * Renvoie l'instance unique de la classe manager. Il faut passer par cette appel pour se servir des fonctions de la classe.
     * Cf classe singleton.
     * @access public
     * @return self
     */
    public static function instance() {
        if(!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 
     * @return \DB\SQL
     */
    
    public function getDb() {
        return $this->db;
    }
    
    /**
     * @param int $id_sujet
     * @return \Sujet\Data
     */
    
    public function getFromId($id_sujet) {
        if($this->idExiste($id_sujet)) {
            $sujet_array = $this->db->exec('SELECT * FROM sujets WHERE id=?', $id_sujet)[0];
            return new Data($sujet_array);
        }
        else
            return NULL;
    }
    
    /**
     * @access public
     * @param array|int $matiere Un id de matière ou un tableau d'ids.
     * @return Array tous les sujets de la matière
     */
    public function getFromMatiere($matiere) {
        if(is_array($matiere)) {
            if(count($matiere) > 0) {
            $placeholders = '';
            $inc = 1;
            foreach($matiere as $id=>$nom) {
                $placeholders .= $inc == 1?'matiere=?':' OR matiere=?';
                $ph_content[$inc] = $id;
                $inc++;
            }
            $results = $this->db->exec('SELECT * FROM sujets WHERE (' . $placeholders . ')', $ph_content);
            }
            else
                $results = array();
        }
        else {
            $results = $this->db->exec('SELECT * FROM sujets WHERE matiere=?', $matiere);
        }
        return $this->results2sujets($results);
    }
    
    /**
     * @access public
     * @return Array Tous les sujets de la BDD.
     */
    public function getAll() {
        $sujets_db = $this->db->exec('SELECT * FROM sujets');
        return $this->results2sujets($sujets_db);
    }
    
    /**
     * @access public
     * @return Array tous les sujets dont l'auteur est l'utilisateur connecté
     */
    public function getMesSujets() {
        $f3 = \Base::instance();
        $sujets_db = $this->db->exec('SELECT * FROM sujets WHERE auteurs=?', $f3->get('SESSION.user')->getId());
        
        return $this->results2sujets($sujets_db);
    }
    
    /**
     * Permet de récupérer tous les sujets qui sont archivés.
     * @access public
     * @return array Un tableau de sujets contenant tous les sujets archivés.
     */
    public function getArchives() {
        $sujets = $this->getAll();
        $archives = array();
        foreach ($sujets as $key => $sujet) {
            if($sujet->estArchive()) {
                $archives[$key] = $sujet;
            }
        }
        return $archives;
    }
    
    /**
     * @access public
     * @param int $id
     * @return bool true si un sujet avec l'id donné existe et est unique
     */
    public function idExiste($id) {
        $sujet = new \DB\SQL\Mapper($this->db,'sujets');
        $nombre_resultats = $sujet->count(array('id=?', $id));
        return ($nombre_resultats == 1);
    }
    
    /**
     * Ajoute un sujet à la BDD
     * @access public
     * @param  Data $sujet
     * @return void
     */
    public function add(Data $sujet) {
        $sujet_db = new \DB\SQL\Mapper($this->db,'sujets');
        $sujet->remplirMapper($sujet_db);
        $sujet_db->id = '';
        $sujet_db->save();
    }
    
    /**
     * Met à jour le sujet dans la BDD
     * @access public
     * @param Data $sujet
     * @return void
     */
    public function update(Data $sujet) {
        if($this->idExiste($sujet->getId())) {
            $sujet_db = new \DB\SQL\Mapper($this->db,'sujets');
            $sujet_db->load(array('id=?', $sujet->getId()));
            $sujet->remplirMapper($sujet_db);
            $sujet_db->save();
        }
        else {
            trigger_error('Le sujet a un ID invalide.');
        }
    }
    
    /**
     * Supprime un sujet de la BDD et détache toutes les questions qui y sont attachées.
     * @access public
     * @param \Sujet\Data $sujet
     * @return void
     */
    public function delete(\Sujet\Data $sujet) {
        if($this->idExiste($sujet->getId())) {
            // On détache d'abord toutes les questions attachées au sujet.
            $question_attachees = $sujet->getQuestions();
            foreach ($question_attachees as $q) {
                $q->detacher($sujet);
            }
            
            // Puis on supprime le sujet de la BDD
            $question_db = new \DB\SQL\Mapper($this->db,'sujets');
            $question_db->erase(array('id=?', $sujet->getId()));
        }
        else {
            trigger_error('Impossible de supprimer le sujet : le sujet a un ID invalide. id : ' . $sujet->getId());
        }
    }
    /**
     * transforme un tableau de résultats en un tableau de questions
     * @access private
     * @param array $mappers
     * @return array Les sujets
     */
    private function results2sujets($mappers) {
        // On renvoie les résultats en transformant les mapper en structure de question
        $sujets = array();
        foreach($mappers as $key=>$sujet) {
            $sujets[$key] = new Data($sujet);
        }
        return $sujets;
    }
}