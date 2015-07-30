<?php

namespace Sujet;

/**
 * Sujet Manager permet de gérer les sujets, notamment les échanges avec la base de données.
 */

class Manager extends \Modele\Manager {
    
    protected function init() {
        $this->nature = 'Sujet';
        $this->table = 'sujets';
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
        return $this->results2objects($results);
    }
    
    /**
     * @access public
     * @return Array tous les sujets dont l'auteur est l'utilisateur connecté
     */
    public function getMesSujets() {
        $f3 = \Base::instance();
        $sujets_db = $this->db->exec('SELECT * FROM sujets WHERE auteurs=?', $f3->get('SESSION.user')->getId());
        
        return $this->results2objects($sujets_db);
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
     * Permet de récupérer tous les concours blancs à venir.
     * @access public
     * @return array Un tableau de sujets contenant tous les concours blancs à venir.
     */
    public function getAVenir() {
        $sujets = $this->getAll();
        $archives = array();
        foreach ($sujets as $key => $sujet) {
            if($sujet->estAVenir()) {
                $archives[$key] = $sujet;
            }
        }
        return $archives;
    }
    
    /**
     * Supprime un sujet de la BDD et détache toutes les questions qui y sont attachées.
     * @access public
     * @param \Sujet\Data $sujet
     * @return void
     */
    public function delete($sujet) {
        if($this->idExiste($sujet->getId())) {
            // On détache d'abord toutes les questions attachées au sujet.
            $question_attachees = $sujet->getQuestions();
            foreach ($question_attachees as $q) {
                $q->detacher($sujet);
            }
            
            // Puis on supprime le sujet de la BDD
            parent::delete($sujet);
        }
        else {
            trigger_error('Impossible de supprimer le sujet : le sujet a un ID invalide. id : ' . $sujet->getId());
        }
    }
}