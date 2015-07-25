<?php

namespace Sujet;

/*
 * Classe contenant les données d'une question
 */

class Data {
    
    /**
     * @var int 
     * @access private
     */
    private $id;
    
    /**
     * @var int 
     * @access private
     */
    private $auteurs;
    
    /**
     * @var int 
     * @access private
     */
    private $matiere;
    
    /**
     * @var int 
     * @access private
     */
    private $numero_cb;
    
    /**
     * @var int 
     * @access private
     */
    private $nombre_questions;
    
    /**
     * @var string 
     * @access private
     */
    private $date;
    
    /**
     * @var string
     * @access private
     */
    private $notions;
    
    /**
     * @access public
     * @param Array $donnees
     */
    public function __construct($donnees) {
        if(!is_array($donnees)) trigger_error('Pour créer une classe Sujet\Data, un array doit être passé en paramètre.');
        $this->hydrate($donnees);
    }
    
    /**
     * @access public
     * @param Array $donnees
     */
    public function hydrate($donnees) {
        foreach($donnees as $cle=>$valeur) {
            $this->$cle = $valeur;
        }
    }
    /**
     * @access public
     * @return int
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * @access public
     * @return int
     */
    public function getMatiere() {
        return $this->matiere;
    }
    
    /**
     * @access public
     * @return int
     */
    public function getAuteurs() {
        return $this->auteurs;
    }
    
    /**
     * @access public
     * @return int
     */
    public function getNumero_cb() {
        return $this->numero_cb;
    }
    
    /**
     * @access public
     * @return string
     */
    public function getDate() {
        return $this->date;
    }
    
    /**
     * Détermine si un sujet et sa correction peuvent être diffusés ou non.
     * @access public
     * @return bool True si le sujet a déja été distribué.
     */
    public function estArchive() {
        $date = $this->getDate();
        // Si le champ date est vide, le sujet n'est pas archivé.
        if($date == '') return 0;
        $date_sujet = explode('/', $date); // 1->jour, 2->mois, 3->année
        $now = explode('/', date('d/m/Y',time()));
        
        return $now[2] > $date_sujet[2] || // année >
                ($now[2] == $date_sujet[2] && ($now[1] > $date_sujet[1] || // année =, mois >
                                        ($now[1] == $date_sujet[1] && $now[0] > $date_sujet[0]))); // année =, mois =, jour >
    }
    
    /**
     * Détermine si le sujet doit être affiché dans le calendrier.
     * @access public
     * @return bool True si le sujet va être distribué prochainement et doit être affiché dans le calendrier.
     */
    public function estAVenir() {
        return (!$this->estArchive()) && $this->getDate() != '';
    }
    
    /**
     * @access public
     * @return String
     */
    public function getNotions() {
        return $this->notions;
    }
    
    /**
     * @access public
     * @return int
     */
    public function getNombre_questions() {
        return $this->nombre_questions;
    }
    
    /**
     * @access public
     * @return int
     */
    public function numQuestionSuiv() {
        return $this->nombre_questions + 1;
    }
    
    /**
     * Incrémente de 1 le nombre de questions du sujet.
     * @access public
     * @return void
     */
    public function incrNbrQuestions() {
        $this->nombre_questions++;
    }
    
    /**
     * Décremente de 1 le nombre de questions du sujet.
     * @access public
     * @return void
     */
    public function decrNbrQuestions() {
        $this->nombre_questions--;
    }
    
    /**
     * remplit un Mapper avec les champs du sujet
     * @access public
     * @param \DB\SQL\Mapper $mapper
     * @return void
     */
    public function remplirMapper(\DB\SQL\Mapper $mapper) {
        foreach ($this as $key=>$value) {
            $mapper->$key = $value;
        }
    }
    
    /**
     * @access public
     * @return array Un tableau contenant les \question\data du sujet
     */
    public function getQuestions() {
        return \Question\Manager::instance()->getFromSujet($this->getId());
    }
    
    /**
     * @access public
     * @param int $num_question
     * @return array Un tableau contenant les \question\data du sujet
     */
    public function getQuestion($num_question) {
        if(0 < $num_question and $num_question <= $this->nombre_questions) {
            return \Question\Manager::instance()->getFromNum($this->getId(), $num_question);
        }
        else
            return NULL;
    }
}