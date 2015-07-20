<?php

namespace Question;

/**
 * Classe appelée pour afficher les pages rélatives aux questions
 */

class View {
    
    /**
     * Affiche la liste éditable des questions dont les matières sont accessible par la personne connectée.
     * @access public
     * @param \Base $f3
     * @return void
     */
    public function liste($f3) {
        if(CIA(SEE_QUESTIONS, 0)) {
            $q_manager = Manager::instance();
            $matieres = \Membre\Manager::instance()->getConnected()->getMatieresInArray();
            $questions = array();
            foreach($matieres as $cle=>$matiere) {
                foreach($q_manager->getFromMatiere($cle) as $question) {
                    $questions[] = $question;
                }
            }

            $f3->set('questions', $questions);
            $f3->set('matiereAffichee', 'all');
            $f3->set('titre', 'Liste de toutes les questions');
            afficherPage('templates/sujets/liste_questions.htm');
        }
        else {
            afficherPage(PAGE_ERREUR);
        }
    }
    
    /**
     * Affiche la liste éditable des questions dont l'auteur est l'utilisateur connecté.
     * @access public
     * @param Base $f3
     * @return void
     */
    public function mesQuestions($f3) {
        if(CIA(SEE_QUESTIONS | ADD_QUESTION, 0)) {
            $membre_connecte = \Membre\Manager::instance()->getConnected();
            $f3->set('questions', $membre_connecte->getQuestions());
            $f3->set('matiereAffichee', 'all');
            $f3->set('titre', 'Liste de mes questions');
            afficherPage('templates/sujets/liste_questions.htm');
        }
        else {
            afficherPage(PAGE_ERREUR);
        }
    }
    
    /**
     * Affiche la page d'ajout/édition d'une question
     * @access public
     * @param \Base $f3
     * @return void
     */
    public function editer($f3){
        /* On initialise tout à 0
         * Cela permet d'éviter les erreur si on utilise des variables inexistantes */
        $f3->set('edition', 0);
        $f3->set('id_question', 0);
        $f3->set('id_sujet', 0);
        $f3->set('num_question', 0);
        $f3->set('matiere', 0);
        
        /* On change les valeurs si nécessaire */
        if($f3->exists('PARAMS.id')) {
            /* Nouvelle question rattachée à un sujet */
            $sujet = \Sujet\Manager::instance()->getFromId($f3->get('PARAMS.id'));
            
            $f3->set('id_sujet', $sujet->getId());
            $f3->set('num_question', $sujet->numQuestionSuiv($f3));
            $condition = CIA(EDIT_SUJET, 0, $sujet);
        }
        elseif($f3->exists('PARAMS.question')) {
            /* Modification d'une question existante */
            $question = Manager::instance()->getFromId($f3->get('PARAMS.question'));
            
            $f3->set('id_sujet', $question->getId_sujet());
            $f3->set('edition', 1);
            $f3->set('id_question', $question->getId());
            $f3->set('question', $question);
            $f3->set('num_question', $question->getNumero_question());
            $f3->set('matiere', $question->getMatiere());
            $condition = CIA(EDIT_QUESTION, 0, $question);
        }
        else {
            $condition = CIA(ADD_QUESTION) && \Membre\Manager::instance()->getConnected()->getMatieres() != 0;
        }
        if($condition) {
            afficherPage('templates/sujets/ajouter_question.htm');
        }
        else
            afficherPage (PAGE_ERREUR);
    }
    
    /**
     * Affiche la page 'Banque de QCM' -> liste des UE pour accèder à la banque correspondante
     * @access public
     * @param \Base $f3
     * @return void
     */
    public function matieresQcms($f3) {
        if(CIA(SEE_QCMS)) {
            afficherPage('templates/sujets/qcms_matieres.htm');
        }
        else
            afficherPage (PAGE_ERREUR);
    }
    
    /**
     * Liste les QCMs disponibles pour une certaine matière
     * @access public
     * @param \Base $f3
     * @return void
     */
    public function qcms($f3) {
        if(CIA(SEE_QCMS)) {
            $matiere = $f3->get('PARAMS.matiere');
            $q_manager = Manager::instance();
            $qcms = $q_manager->selectQcms($q_manager->getFromMatiere($matiere));
            $f3->set('questions', $qcms);

            afficherPage('templates/sujets/qcms.htm');
        }
        else
            afficherPage (PAGE_ERREUR);
    }
}