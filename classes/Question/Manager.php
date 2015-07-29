<?php

namespace Question;

/* 
 * Question Manager permet de gérer les questions, notamment les échanges avec la base de données
 */

class Manager {
    private $db;
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
            self::$instance = new Manager();
        }
        return self::$instance;
    }
    
    public function getDb() {
        return $this->db;
    }
    /**
     * @access public
     * @param int $id_question
     * @return Data La question a partir de son ID
     */
    public function getFromId($id_question) {
        if($this->idExiste($id_question)) {
            $question_array = $this->db->exec('SELECT * FROM questions WHERE id=?', $id_question)[0];
            return new Data($question_array);
        }
        else
            return NULL;
    }
    
    /**
     * @access public
     * @param int $id_sujet
     * @param int $num_question
     * @return Data La question a partir de son sujet et numéro de question
     */
    public function getFromNum($id_sujet, $num_question) {
        $question_array = $this->db->exec('SELECT * FROM questions WHERE numero_question=:num_q AND id_sujet=:sujet',
                array('num_q' => $num_question,
                      'sujet' => $id_sujet));
        if(count($question_array) > 0) {
            return new Data($question_array[0]);
        }
        else
            trigger_error ('Il n\'y a pas de question ' . $num_question . ' dans le sujet ' . $id_sujet);
        
    }
    
    /**
     * Renvoie un tableau des questions contenues dans la sujet donnée, classé par numero_question croissant.
     * @access public
     * @param int $id_sujet
     * @return Array Le tableau des questions du sujet choisi.
     */
    public function getFromSujet($id_sujet) {
        $questions_db = $this->db->exec('SELECT * FROM questions WHERE id_sujet=? ORDER BY numero_question', $id_sujet);
        return $this->results2questions($questions_db);
    }
    
    /**
     * @access public
     * @param int $matiere
     * @return Array Le tableau des questions de la matière choisie.
     */
    public function getFromMatiere($matiere) {
        $questions_db = $this->db->exec('SELECT * FROM questions WHERE matiere=?', $matiere);
        return $this->results2questions($questions_db);
    }
    
    /**
     * Retourne toutes les questions de la BDD
     * @access public
     * @return void
     */
    public function getAll() {
        $questions_db = $this->db->exec('SELECT * FROM questions');
        return $this->results2questions($questions_db);
    }
    
    /**
     * Retourne toutes les questions d'un membre passé en paramètre. Il s'agit des questions dont le membre est l'auteur
     * et dont la matière est valide.
     * @access public
     * @param \Membre\Data $membre Le membre dont on veut obtenir les questions.
     * @return void
     */
    public function getFromMembre($membre) {
        $num_matieres = array_flip($membre->getMatieresInArray());
        $inc = 1;
        $placeholders = '';
        $ph_content[1] = $membre->getId();
        foreach($num_matieres as $num) {
            $placeholders .= $inc == 1?'matiere=?' : ' OR matiere=?';
            $inc++;
            $ph_content[] = $num;
        }
        
        if($placeholders != '') {
            $questions_db = $this->db->exec('SELECT * FROM questions WHERE auteurs=? AND (' . $placeholders . ')', $ph_content);
            return $this->results2questions($questions_db);
        }
        else {
            return array();
        }
    }
    
    /**
     * Retourne true si un sujet avec l'id donné existe et est unique
     * @access public
     * @return bool
     */
    public function idExiste($id) {
        $question = new \DB\SQL\Mapper($this->db,'questions');
        $nbr_resultats = $question->count(array('id=?', $id));
        return ($nbr_resultats == 1);
    }
    
    /**
     * Ajoute une question à la BDD
     * @access public
     * @param \Question\Data $question
     * @return void
     */
    public function add(\Question\Data $question) {
        if(!$this->idExiste($question->getId())) {
        $question_db = new \DB\SQL\Mapper($this->db,'questions');
        $question->remplirMapper($question_db);
        $question_db->id = '';
        $question_db->save();
        }
        else {
            trigger_error('Impossible d\'ajouter la question : La question ' . $question->getId() . ' existe déja.');
        }
    }
    
    /* Met à jour la question dans la BDD
     * @access public
     * @param question Data
     * @return void
     */
    public function update(Data $question) {
        if($this->idExiste($question->getId())) {
            $question_db = new \DB\SQL\Mapper($this->db,'questions');
            $question_db->load(array('id=?', $question->getId()));
            $question->remplirMapper($question_db);
            $question_db->save();
        }
        else {
            trigger_error('Impossible de mettre à jour la question : la question a un ID invalide. id : ' . $question->getId());
        }
    }
    
    /**
     * Supprime une question de la BDD
     * @access public
     * @param \Question\Data $question
     * @return void
     */
    public function delete(\Question\Data $question) {
        if($this->idExiste($question->getId())) {
            $question_db = new \DB\SQL\Mapper($this->db,'questions');
            $question_db->erase(array('id=?', $question->getId()));
        }
        else {
            trigger_error('Impossible de supprimer la question : la question a un ID invalide. id : ' . $question->getId());
        }
    }
    
    /**
     * Selectionne parmis toutes les questions passées en paramètre, celles qui sont banquée.
     * @param array $questions
     * @return array Contient toutes les questions banquées
     */
    public function selectQcms(Array $questions) {
        $qcms = array(); // Le tableau des qcms à renvoyer
        foreach($questions as $key=>$question) {
            if($question->estBanquee()) {
                $qcms[$key] = $question;
            }
        }
        return $qcms;
    }
    
    /**
     * Permet de rechercher un terme parmi les questions indépendantes. Cela peut être un ID de question
     * ou un élément de la question.
     * @access public
     * @param string $terme
     * @return array Un tableau de questions contenant les résultats de la recherche.
     */
    public function rechercher($terme) {
        if($terme == '') {
            return $this->getAll();
        }
        else {
            $results = $this->db->exec('SELECT * FROM questions WHERE id LIKE :terme OR question LIKE :terme',
                    array('terme' => '%'.$terme.'%'));
        }
        return $this->results2questions($results);
    }
    /**
     * transforme un tableau de résultats en un tableau de questions
     * @access private
     * @param array $mappers
     * @return \Question\Data
     */
    private function results2questions($mappers) {
        // On renvoie les résultats en transformant les mapper en structure de question
        $questions = array();
        foreach($mappers as $key=>$question) {
            $questions[$key] = new Data($question);
        }
        return $questions;
    }
}