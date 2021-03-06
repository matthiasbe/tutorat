<?php

namespace Sujet;

/*
 * Classe contenant les données d'une question
 */

class Data extends \Modele\Data {
    
    const TYPE_CB_PRERENTREE = -1;
    const TYPE_CB_CBG = -2;
    
    /**
     * @var int 
     * @access private
     */
    protected $auteurs;
    
    /**
     * @var int 
     * @access private
     */
    protected $matiere;
    
    /**
     * @var int 
     * @access private
     */
    protected $numero_cb;
    
    /**
     * @var int 
     * @access private
     */
    protected $nombre_questions;
    
    /**
     * @var string 
     * @access private
     */
    protected $date;
    
    /**
     * @var string
     * @access private
     */
    protected $notions;
    
    /**
     * @access public
     * @return int
     */
    public function getMatiere() {
        return $this->matiere;
    }
    
    /**
     * @access public
     * @param string $matiere La matière sujet. Doit être un entier.
     * @throws \Exception
     * @return void
     */
    public function setMatiere($matiere) {
        $f3 = \Base::instance();
        if($f3->exists('matieres.' . $matiere)) {
            $this->matiere = $matiere;
        }
        else {
            throw new \Exception ('Matière de sujet invalide : ' . $matiere);
        }
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
     * @param string $auteurs L'id de l'auteur du sujet.
     * @throws \Exception
     * @return void
     */
    public function setAuteurs($auteurs) {
        if(is_numeric($auteurs)) {
            $this->auteurs = $auteurs;
        }
        else {
            throw new \Exception ('Auteurs de sujet invalides : ' . $auteurs);
        }
    }
    
    /**
     * @access public
     * @return int
     */
    public function getNumero_cb() {
        switch($this->numero_cb) {
            case self::TYPE_CB_PRERENTREE:
                return 'Pré-rentrée';
            case self::TYPE_CB_CBG;
                return 'CB Général';
            default:
                return 'CB' . $this->numero_cb;
        }
    }
    
    /**
     * @access public
     * @param string $num_cb L'id de l'auteur du sujet.
     * @throws \Exception
     * @return void
     */
    public function setNumero_cb($num_cb) {
        if(is_numeric($num_cb)) {
            $this->numero_cb = $num_cb;
        }
        else {
            throw new \Exception ('Numéro de cb invalide : ' . $num_cb);
        }
    }
    
    /**
     * @access public
     * @return string
     */
    public function getDate() {
        return $this->date;
    }
    
    /**
     * Change la date du sujet.
     * @access public
     * @param string $date Date au format JJ/MM/AAAA
     * @throws \Exception
     * @return void
     */
    public function setDate($date) {
        if(preg_match('#^(0?[1-9]|[1-2][0-9]|3[01])/(0?[1-9]|1[012])/[0-9]{4}$#', $date) OR $date == '') {
            $this->date = $date;
        }
        else {
            throw new \Exception ('Format de date invalide. La date doit être au format JJ/MM/AAAA');
        }
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
     * @param string $notions Les notions du sujet.
     * @throws \Exception
     * @return void
     */
    public function setNotions($notions) {
        if(is_string($notions)) {
            $this->notions = $notions;
        }
        else {
            throw new \Exception ('Notions invalides : ' . $notions);
        }
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
     * @param string $nombre_questions Les notions du sujet.
     * @throws \Exception
     * @return void
     */
    public function setNombre_questions($nombre_questions) {
        if(is_numeric($nombre_questions)) {
            $this->nombre_questions = $nombre_questions;
        }
        else {
            throw new \Exception ('Nombre de questions invalide : ' . $nombre_questions);
        }
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