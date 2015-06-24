<?php

class Sujet
{
    public $id;
    
    /* 
     * Partie VUE
     */
    
    public function afficherListeSujets($f3) {
        $f3->set('result', $this->getSujets($f3));
        $f3->set('matiereAffichee', 'all');
        $f3->set('liste_sujets_active', 1);
        $f3->set('titre', 'Liste de tous les sujets');
        
        afficherPage($f3, 'templates/sujets/liste.htm');
    }
    
    public function afficherMesSujets($f3) {
        $f3->set('result', $this->getMesSujets($f3));
        $f3->set('matiereAffichee', 'all');
        $f3->set('titre', 'Liste de mes sujets');
        
        afficherPage($f3, 'templates/sujets/liste.htm');
    }
    
    public function afficherListeQuestions($f3) {
        $f3->set('questions', $this->getAllQuestions($f3));
        $f3->set('matiereAffichee', 'all');
        $f3->set('titre', 'Liste de toutes les questions');
        afficherPage($f3, 'templates/sujets/liste_questions.htm');
    }
    
    public function afficherMesQuestions($f3) {
        $f3->set('questions', $this->getMesQuestions($f3));
        $f3->set('matiereAffichee', 'all');
        $f3->set('titre', 'Liste de mes questions');
        afficherPage($f3, 'templates/sujets/liste_questions.htm');
    }
    
    public function afficherAjouterSujet($f3) {
        afficherPage($f3, 'templates/sujets/ajouter.htm');
    }
    
    public function afficherAjouterQuestion($f3){
        if($f3->exists('PARAMS.id')) {
            /* Question rattachée à un sujet */
            $this->id = $f3->get('PARAMS.id');
            $f3->set('id_sujet', $f3->get('PARAMS.id'));
            if($f3->exists('PARAMS.question')) {
                /* Modification d'une question existante */
                $f3->set('num_question', $f3->get('PARAMS.question'));
                $f3->set('question', $this->getQuestionFromNum($f3, $f3->get('PARAMS.question')));
            }
            else {
                /* Ajout d'une nouvelle question */
                $f3->set('num_question', 0);
                $f3->set('num_question_suiv', $this->numQuestionSuiv($f3));
            }
        }
        else {
            /* Question indépendante */
            $this->id = 0;
            $f3->set('id_sujet', 0);
            if($f3->exists('PARAMS.question')) {
                /* Modification d'une question existante */
                $f3->set('num_question', $f3->get('PARAMS.question'));
                $f3->set('question', $this->getQuestion($f3, $f3->get('PARAMS.question')));
            }
            else {
                /* Ajout d'une nouvelle question */
                $f3->set('num_question', 0);
            }
        }
        
        afficherPage($f3, 'templates/sujets/ajouter_question.htm');
    }
    
    public function afficherSujet($f3) {
        $this->id = $f3->get('PARAMS.id');
        
        $f3->set('sujet', $this->getSujet($f3));
        $f3->set('questions', $this->getQuestions($f3));

        $f3->set('reponse', function($reponse, $num_reponse){
            $base2 = base_convert($reponse, '10', '2');
            return getBit($base2, $num_reponse);
        });
        
        
        
        afficherPage($f3, 'templates/sujets/sujet.htm');
    }
    
    function renduPdf($f3) {
        $this->id = $f3->get('PARAMS.id');
        $pdf = new Pdf;
        $pdf->rendu($f3, $this);
        $template = new Template;
        echo $template->render($pdf->rendu($f3, $this));
    }
    
    /* 
     * Partie CONTROLLEUR
     */
    
    
    public function ajouterSujet($f3) {
        $this->id = '0';
        $this->sauvegarderSujet($f3);
    }
    
    public function ajouterQuestion($f3) {
        $this->id = $f3->get('PARAMS.id');
        $this->sauvegarderQuestion($f3);
        $f3->reroute('/sujets/' . $this->id);
    }
    
    public function ajouterQuestionIndependante($f3) {
        $this->id = 0;
        $this->sauvegarderQuestion($f3);
        $f3->reroute('/sujets/ajouter_question/');
    }
    
    public function modifierChamp($f3) {
//        if($this->estAutoriseAModifier($f3, $f3->get('POST.id'), $f3->get('POST.champ')))
            $this->majChamp($f3);
//        else
//                echo 'Vous n\'avez les autorisations nécessaires pour modifier ce champ';
    }

    // Détache la question du sujet -> POST.question est un numero de question dans le sujet
    public function detacherQuestion($f3) {
        $this->id = $f3->get('POST.sujet');
        $question = $f3->get('POST.question');
        
        $this->enleverQuestion($f3, $question);
        
        for($i = $question; $i <= $this->nbrQuestions($f3); $i++) {
            $this->decrNumQuestion($f3, $i);
        }
        $this->decrNbrQuestions($f3);
    }

    // Attache la question au sujet ->  -> POST.question est un id
    public function attacherQuestion($f3) {
        $this->id = $f3->get('POST.sujet');
        $question = $f3->get('POST.question');
        
        //Set id_sujet
        $this->attachQuestion($f3, $question);
        
        // Set numero_question
        $this->ajouterEnFin($f3, $question);
                
        // Set nbr_question
        $this->incNbrQuestions($f3);
    }
    
    public function supprimerQuestion($f3) {
        $question = $f3->get('POST.question');
        if($this->estIndependante($f3, $question)) {
            $this->deleteQuestion($f3, $question);
        }
        else
            echo "La question est rattachée à un sujet. Veuillez la détacher avant de la supprimer.";
    }
    
    public function estIndependante($f3, $question) {
        $q = $this->getQuestion($f3, $question);
        if($q['sujet_id'] == 0) return 1;
        else return 0;
    }
    
    public function rechercherQuestion($f3) {
        $terme = $f3->get('POST.recherche');
        $resultats = $this->rechercher($f3, $terme);
        
        foreach($resultats as $question) {
            if($question['sujet_id'] == 0) {
                echo '#' . $question['id'] . ' [Q:' . $question['question'] . '] '
                    . '<span onclick="attacherQuestion(' . $question['id'] . ')" class="bouton glyphicon glyphicon-resize-small"></span>';
            }
        }
    }
    
    // Echange la question avec celle de numero_question juste superieur
    public function descendreQuestion($f3) {
        $this->id = $f3->get('POST.sujet');
        $num_question = $f3->get('POST.num_question');
        $id_question = $f3->get('POST.id_question');
        
        $this->decrNumQuestion($f3, $num_question + 1);
        $this->incrNumQuestion($f3, $id_question);
    }
    
    // Echange la question avec celle de numero_question juste inferieur
    public function monterQuestion($f3) {
        $this->id = $f3->get('POST.sujet');
        $num_question = $f3->get('POST.num_question');
        
        $id_question_inferieur = $this->getQuestionFromNum($f3, $num_question - 1)['id'];
        
        $this->decrNumQuestion($f3, $num_question);
        $this->incrNumQuestion($f3, $id_question_inferieur);
    }


    /* 
     * Partie MODELE
     */
    
    
    public function getSujets($f3) {
        $db = $f3->get('Bdd');
        return $db->exec('SELECT * FROM sujets');
    }
    
    public function getMesSujets($f3) {
        $db = $f3->get('Bdd');
        return $db->exec('SELECT * FROM sujets WHERE auteurs=?', $f3->get('SESSION.user')->id);
    }
    
    public function getSujet($f3) {
        $db = $f3->get('Bdd');
        return $db->exec('SELECT * FROM sujets WHERE id=?', $this->id)[0];
    }
    
    // Sélectionner toutes les questions du sujet
    public function getQuestions($f3) {
        $db = $f3->get('Bdd');
        return $db->exec('SELECT * FROM questions WHERE sujet_id=? ORDER BY numero_question', $this->id);
    }
    
    // Sélectionner toutes les questions de la BDD
    public function getAllQuestions($f3) {
        $db = $f3->get('Bdd');
        return $db->exec('SELECT * FROM questions');
    }
    
    public function getMesQuestions($f3) {
        $db = $f3->get('Bdd');
        return $db->exec('SELECT * FROM questions WHERE auteurs=?', $f3->get('SESSION.user')->id);
    }
    
    public function getQuestion($f3, $id_question) {
        $db = $f3->get('Bdd');
        return $db->exec('SELECT * FROM questions WHERE id=?', $id_question)[0];
    }
    
    public function getQuestionFromNum($f3, $num_question) {
        $db = $f3->get('Bdd');
        return $db->exec('SELECT * FROM questions WHERE numero_question=:num_q AND sujet_id=:sujet',
                array('num_q' => $num_question,
                      'sujet' => $this->id))[0];
    }
    
    public function nbrQuestions($f3) {
        $db = $f3->get('Bdd');
        $sujet = new DB\SQL\Mapper($db,'sujets');
        $sujet->load(array('id=?', $this->id));
        return $sujet->nombre_questions;
    }
    
    public function numQuestionSuiv($f3) {
        return $this->nbrQuestions($f3) + 1;
    }
    
    // Met numero_question à nbr_question + 1
    public function ajouterEnFin($f3, $question) {
        $db = $f3->get('Bdd');
        $record = new DB\SQL\Mapper($db,'questions');
        $record->load(array('id=?', $question));
        $record->numero_question = $this->numQuestionSuiv($f3);
        $record->save();
    }


    public function sauvegarderSujet($f3) {
        $db = $f3->get('Bdd');
        $sujet = new DB\SQL\Mapper($db,'sujets');
        
        $sujet->copyfrom('POST');
        $sujet->nombre_questions = 0;
        $sujet->auteurs = $f3->get('SESSION.user')->id;
        
        $sujet->save();
        $id = $db->lastInsertId();
        
        $f3->reroute('/sujets/' . $id);
    }
    
    public function sauvegarderQuestion($f3) {
        $db = $f3->get('Bdd');
        $question = new DB\SQL\Mapper($db,'questions');
        
        
        $question->copyfrom('POST');
        
        if($this->id) {
            $this->incNbrQuestions($f3);
            $question->sujet_id = $this->id;
        }
        
        
        $question->reponse = $f3->get('POST.reponse1')
                +$f3->get('POST.reponse2')*2
                +$f3->get('POST.reponse3')*4
                +$f3->get('POST.reponse4')*8
                +$f3->get('POST.reponse5')*16;
        
        $question->numero_question = $this->nbrQuestions($f3);
        $question->auteurs = $f3->get('SESSION.user')->id;
        
        $question->save();
    }
    
    public function incNbrQuestions($f3) {
        $db = $f3->get('Bdd');
        $db->exec('UPDATE sujets SET nombre_questions = nombre_questions + 1 WHERE id=?', $this->id);
    }
    
    public function decrNbrQuestions($f3) {
        $db = $f3->get('Bdd');
        $db->exec('UPDATE sujets SET nombre_questions = nombre_questions - 1 WHERE id=?', $this->id);
    }
    
    public function supprimerSujet($f3) {
        $this->id = $f3->get('POST.id');
        $db = $f3->get('Bdd');
        $db->exec('UPDATE questions SET sujet_id=0 WHERE sujet_id=?', $this->id);
        $db->exec('DELETE FROM sujets WHERE id=?', $this->id);
    }
    
    public function deleteQuestion ($f3, $question) {
        $db = $f3->get('Bdd');
        $db->exec('DELETE FROM questions WHERE id=?', $question);
    }
    
    // question est ici un numero_question
    function decrNumQuestion($f3, $num_question) {
        $db = $f3->get('Bdd');
        $db->exec('UPDATE questions SET numero_question = numero_question - 1 WHERE numero_question=:num_q AND sujet_id=:sujet',
                array('num_q' => $num_question,
                      'sujet' => $this->id));
    }
    
    // question est ici un id
    function incrNumQuestion($f3, $question_id) {
        $db = $f3->get('Bdd');
        $db->exec('UPDATE questions SET numero_question = numero_question + 1 WHERE id=?',$question_id);
    }
    
    // Met l'id_sujet à 0 (détache la question du sujet)
    function enleverQuestion($f3, $question) {
        $db = $f3->get('Bdd');
        $db->exec('UPDATE questions SET sujet_id=0 WHERE numero_question=:num_q AND sujet_id=:sujet',
                array(
                    'sujet' => $this->id,
                    'num_q' => $question,
                ));
    }
    
    // Met l'id_sujet à 0 (détache la question du sujet)
    function attachQuestion($f3, $question) {
        $db = $f3->get('Bdd');
        $db->exec('UPDATE questions SET sujet_id=:sujet WHERE id=:num_q',
                array(
                    'sujet' => $this->id,
                    'num_q' => $question,
                ));
    }
    
    public function majChamp($f3) {
        $db = $f3->get('Bdd');
        $question = $f3->get('POST.question');
        $correction = $f3->get('POST.correction');
        $item= $f3->get('POST.item');
        $sujet = $f3->get('POST.sujet');
        $data = $f3->get('POST.data');

        
        if(!$item)
            $champ = 'question';
        else 
            $champ = ($correction?'correction':'item').$item;
        
        $db->exec('UPDATE questions SET ' . $champ . '=:valeur WHERE numero_question=:num_q AND sujet_id=:sujet',
                array(
                    'valeur' => $data,
                    'sujet' => $sujet,
                    'num_q' => $question,
                ));
    }
    
    public function rechercher($f3, $terme) {
        $db = $f3->get('Bdd');
        $results = new DB\SQL\Mapper($db,'questions');
        return $results->find(array('id=?', $terme));
    }
}