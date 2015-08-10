<?php

namespace Question;

/* 
 * Question Manager permet de gérer les questions, notamment les échanges avec la base de données
 */

class Manager extends \Modele\Manager {
    
    protected function init() {
        $this->nature = 'Question';
        $this->table = 'questions';
    }
    
    protected function beforeAdd($question) {
        $type = \Alerte\Data::ALERT_TYPE_CREATION;
        $last_id = $this->getLastInserted(); // TODO
        $membres = \Membre\Manager::instance()->getAll();
        $alerte = new \Alerte\Data(array(
            'contenu_id' => $question->getId(),
            'contenu_classe' => 'question',
            'type' => $type,
            'membres' => $membres
        ));
        print_r($question);
//        \Alerte\Manager::instance()->add($alerte);
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
        return $this->results2objects($questions_db);
    }
    
    /**
     * @access public
     * @param int $matiere
     * @return Array Le tableau des questions de la matière choisie.
     */
    public function getFromMatiere($matiere) {
        $questions_db = $this->db->exec('SELECT * FROM questions WHERE matiere=?', $matiere);
        return $this->results2objects($questions_db);
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
            return $this->results2objects($questions_db);
        }
        else {
            return array();
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
        return $this->results2objects($results);
    }
}