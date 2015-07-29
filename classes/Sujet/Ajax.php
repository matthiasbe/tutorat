<?php

namespace Sujet;

/* 
 * Reçoit les requètes ajax
 */

class Ajax {
    /**
     * Détache la question du sujet -> POST.sujet est un id de sujet et 
     * POST.question est un numero de question dans le sujet.
     * @access public
     * @param Base $f3
     * @return void
     */
    public function detacherQuestion($f3) {
        $connected = \Membre\Manager::instance()->getConnected();
        $q_manager = \Question\Manager::instance();
        $sujet = Manager::instance()->getFromId($f3->get('POST.sujet'));
        $question = $q_manager->getFromNum($sujet->getId(), $f3->get('POST.question'));
        
        if(CIA('ATTACH', 0, $sujet)) {
            $question->detacher($sujet);
            Manager::instance()->update($sujet);
            $q_manager->update($question);

            // On décremente toutes les questions ayant un numero supérieur
            for($i = $question->getNumero_question()+1; $i <= $sujet->getNombre_questions()+1; $i++) {
                echo $i;
                $q = $q_manager->getFromNum($sujet->getId(), $i);
                $q->decrNumQuestion();
                $q_manager->update($q);
            }
        }
        else
            echo ERREUR;
    }

    /**
     * Attache la question au sujet -> POST.sujet est un id de sujet et 
     * POST.question id de question.
     * @access public
     * @param Base $f3
     * @return void
     */
    public function attacherQuestion($f3) {
        $q_manager = \Question\Manager::instance();
        $sujet = Manager::instance()->getFromId($f3->get('POST.sujet'));
        $question = $q_manager->getFromId($f3->get('POST.question'));
        
        if(CIA(ATTACH, 0, $sujet)) {
            // Modifie le sujet et la question
            $question->rattacher($sujet);
            // Mais il faut enregistrer !
            $q_manager->update($question);
            Manager::instance()->update($sujet);
        }
        else
            echo ERREUR;
    }
    
    /**
     * Echange la question avec celle de numero_question juste supérieur
     * POST.sujet correspond a l'id du sujet.
     * POST.num_question correspond au numéro de la question à descendre.
     * @param Base $f3
     * @return void
     */
    public function descendreQuestion($f3) {
        $sujet = \Sujet\Manager::instance()->getFromId($f3->get('POST.sujet'));
        $connected = \Membre\Manager::instance()->getConnected();
        if(CIA(EDIT_SUJET, 0, $sujet)) {
            $question = $sujet->getQuestion($f3->get('POST.num_question'));
            $question_sup = $sujet->getQuestion($f3->get('POST.num_question') + 1);

            if($question_sup != NULL) {
                $question->incrNumQuestion();
                $question_sup->decrNumQuestion();

                // On enregistre
                \Question\Manager::instance()->update($question);
                \Question\Manager::instance()->update($question_sup);
            }
            else {
                echo 'Impossible de descendre la dernière question.';
            }
        }
        else {
            echo ERREUR;
        }
    }
    
    /**
     * Echange la question avec celle de numero_question juste inférieur.
     * POST.sujet correspond a l'id du sujet.
     * POST.num_question correspond au numéro de la question à monter.
     * @param Base $f3
     * @return void
     */
    public function monterQuestion($f3) {
        $sujet = \Sujet\Manager::instance()->getFromId($f3->get('POST.sujet'));
        $connected = \Membre\Manager::instance()->getConnected();
        if(CIA(EDIT_SUJET, 0, $sujet)) {
            $question = $sujet->getQuestion($f3->get('POST.num_question'));
            $question_inf = $sujet->getQuestion($f3->get('POST.num_question') - 1);

            if($question_inf != NULL) {
                $question_inf->incrNumQuestion();
                $question->decrNumQuestion();

                // On enregistre
                \Question\Manager::instance()->update($question);
                \Question\Manager::instance()->update($question_inf);
            }
            else {
                echo 'Impossible de monter la première question.';
            }
        }
        else {
            echo ERREUR;
        }
    }
    
    /**
     * Supprimer un sujet de la BDD.
     * POST.id contient l'id du sujet à supprimer.
     * @access public
     * @param Base $f3 L'instance de la classe de base de FatFreeFramework. (passée en argument automatiquement par f3 lors du routing)
     * @return void
     */
    public function supprimer($f3) {
        $s_manager = Manager::instance();
        $sujet = $s_manager->getFromId($f3->get('POST.id'));
        $connected = \Membre\Manager::instance()->getConnected();
        if((CIA(DELETE_SUJET) && $connected->gereMatiere($sujet->getMatiere())) || $connected->isAuteur($sujet)) {
            if($sujet != NULL) {
                $s_manager->delete($sujet);
            }
            else {
                trigger_error('Impossible de supprimer le sujet : id inconnu. id : ' . $f3->get('POST.id'));
            }
        }
        else {
            echo ERREUR;
        }
    }
    
    /**
     * Modifie la date d'un sujet.
     * POST.sujet id du sujet dont on modifie la date.
     * POST.date nouvelle date du sujet.
     * @access public
     * @param Base $f3
     * @return void
     */
    public function modifierDate($f3) {
        $sujet = \Sujet\Manager::instance()->getFromId($f3->get('POST.sujet'));
        $nouvelle_date = $f3->get('POST.date');
        if(CIA(EDIT_SUJET, 0, $sujet)) {
            try {
                $sujet->setDate($nouvelle_date);
                \Sujet\Manager::instance()->update($sujet);
            } catch (\Exception $exc) {
                echo $exc->getMessage();
            }

        }
        else {
            echo ERREUR;
        }
    }
}