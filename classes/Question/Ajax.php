<?php

namespace Question;

/* 
 * Reçoit les requètes ajax
 */

class Ajax {
    
    /**
     * Condition pour l'édition : (l'auteur est connecté) ou (le connecté est responsable de la matière de la question et
     * est autorisé à éditer une question)
     * Condition pour l'ajout : (le connecté est responsable de la matière de la question et
     * est autorisé à ajouter une question)
     * @access public
     * @param Base $f3
     * @return void
     */
    public function save($f3) {
        /**
         * 4 configurations possibles en tout 23 champs à renseigner dont 17 toujours donnés
         * ce qui nous donne 6 champs qui ne sont pas toujours renseignés : id, id_sujet, matiere, auteurs, numero_question et date
         * date n'est pas à renseigner en cas d'ajout (fait automatique par mySQL)
         * 
         *** Config 1 : Ajout d'une question indépendante
         * 4 champs/6 renseignés, il manque date et auteurs mais date falcultatif
         * champs à rajouter : AUTEUR
         * 
         *** Config 2 : Ajout d'une question rattachée
         * 4 champs/6 renseignés, il manque date et auteurs mais date falcultatif
         * Remarque : matiere est renseigné mais vaut 0
         * Remarque : On doit mettre à jour le sujet
         * champs à rajouter : AUTEUR et MATIERE
         * 
         *** Config 3 : Edition d'une question indépendante
         * 4 champs/6 renseignés, il manque date et auteurs mais date falcultatif
         * champs à rajouter : AUTEUR
         * 
         *** Config 4 : Edition d'une question rattachée
         * 4 champs/6 renseignés, il manque date et auteurs mais date falcultatif
         * Remarque : matiere est renseigné mais vaut 0
         * champs à rajouter : AUTEUR et MATIERE
         * 
         * Conclusion : les config 1 et 3 ainsi que 2 et 4 peuvent être regroupées
         */
        $question = new Data($f3->get('POST.donnees'));
        // On donne un auteur : l'utilisateur connecté ou celui du sujet rattaché
        $question->determinerAuteurs();
        $connected = \Membre\Manager::instance()->getConnected();
        
        // On remplit les champs manquants (voir ci dessus)
        if($question->estRattachee()) {
            // Configuration 2 et 4 : la question est rattachée à un sujet
            $sujet = \Sujet\Manager::instance()->getFromId($question->getId_sujet());
            
            // On donne une matiere et on met à jour le sujet (nbrQuestions++)
            if(!$question->getId()) { // CONFIG 2 : ajout
                $question->rattacher($sujet);
                // Enregistrement
                if(CIA(ADD_QUESTION) && $connected->gereMatiere($question->getMatiere()))  {
                    Manager::instance()->add($question);
                    \Sujet\Manager::instance()->update($sujet);
                }
                else echo ERREUR;
            }
            else { // CONFIG 4 édition
                $question->setMatiere($sujet->getMatiere());
                // Enregistrement
                if(CIA(EDIT_QUESTION, 0, $question)) Manager::instance()->update($question);
                else echo ERREUR;
            }
        }
        else {
            // Configuration 1 et 3 : la question est indépendante
            if(!$question->getId()) { // CONFIG 1 : ajout
                // Enregistrement
                if(CIA(ADD_QUESTION) && $connected->gereMatiere($question->getMatiere()))
                        Manager::instance()->add($question);
                else echo ERREUR;
            }
            else { // CONFIG 3 édition
                print_r($question);
                // Enregistrement
                if(CIA(EDIT_QUESTION, 0, $question))
                    Manager::instance()->update($question);
                else echo ERREUR;
            }
        }
    }
    
    /**
     * Supprime la question de la BDD si elle est indépendante.
     * POST.question correspond à l'id de la question à supprimer.
     * Condition : (l'auteur est connecté) ou (le connecté est responsable de la matière de la question et
     * est autorisé à supprimer une question)
     * @access public
     * @param Base $f3
     * @return void
     */
    public function supprimer($f3) {
        $q_manager = Manager::instance();
        $question = $q_manager->getFromId($f3->get('POST.question'));
        $connected = \Membre\Manager::instance()->getConnected();
        
        if((CIA(DELETE_QUESTION) && $connected->gereMatiere($question->getMatiere())) || $connected->isAuteur($question)) { 
            if($question->estIndependante()) {
                $q_manager->delete($question);
            }
            else {
                echo "La question est rattachée à un sujet ou fait partie de la banque de QCMs."
                . " Veuillez la détacher avant de la supprimer.";
            }
        }
        else {
            echo ERREUR;
        }
    }
    
    /**
     * Banque la question si elle est indépendante.
     * POST.Question : id de la question à banquer.
     * @access public
     * @param Base $f3
     * @return void
     */
    public function banquer($f3) {
        if(CIA(BANK)) {
            $question = Manager::instance()->getFromId($f3->get('POST.question'));
            if($question->estIndependante()) {
                $question->banquer();
                Manager::instance()->update($question);
            }
            else {
                echo "La question est rattachée à un sujet ou fait déjà partie de la banque de QCMs."
                . " Veuillez la détacher avant de la banquer.";
            }
        }
        else
            echo ERREUR;
    }
    
    /**
     * Débanque la question si elle est banquée.
     * POST.Question : id de la question à débanque.
     * @access public
     * @param Base $f3
     * @return void
     */
    public function debanquer($f3) {
        if(CIA(BANK)) {
            $question = Manager::instance()->getFromId($f3->get('POST.question'));
            if($question->estBanquee()) {
                $question->debanquer();
                Manager::instance()->update($question);
            }
            else {
                echo "La question n'est pas banquée.";
            }
        }
        else
            echo ERREUR;
    }
    
    /**
     * Appelée par AJAX. Permet de rechercher un question à partir d'un champ.
     * POST.recherche correspond au terme à rechercher. Cela peut être un id, être contenu dans la
     * question. S'il est vide, seront affichés les 20 première questions.
     * POST.matiere permet de filtrer les questions par matière.
     * @access public
     * @param type $f3
     * @return void
     */
    public function rechercher($f3) {
        $matiere = $f3->get('POST.matiere');
        $connected = \Membre\Manager::instance()->getConnected();
        
        if($connected->gereMatiere($matiere)) {
            $terme = $f3->get('POST.recherche');
            $resultats = Manager::instance()->rechercher($terme);

            foreach($resultats as $question) {
                if($question->getId_sujet() == 0 and $question->getMatiere() == $matiere) {
                    echo '#' . $question->getId() . ' ' . $question->getQuestion() .''
                        . '<span onclick="attacherQuestion(' . $question->getId() . ')" class="bouton glyphicon glyphicon-resize-small"></span><br/>';
                }
            }
        }
        else {
            echo ERREUR;
        }
    }
}