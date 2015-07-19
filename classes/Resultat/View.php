<?php

namespace Resultat;

/* 
 * Classe appelée pour afficher les pages rélatives aux résultats
 */

class View {
    
    /**
     * Fonction qui récupère un fichier avec la méthode POST via un formulaire.
     * Elle parse ensuite ce fichier pour créer et enregistrer une série de résultats pour un sujet donné et différents membres.
     * @access public
     * @param type $f3
     * @return void
     */
    public function uploadResults($f3) {
        if(CIA(UPLOAD_RESULTS)) {
            if($f3->exists('POST.reponses')) {

                $fichier_resultats = $f3->get('POST.reponses');
                $lignes = file($fichier_resultats);

                try {
                    Manager::instance()->parserEtEnregistrer($lignes);
                    echo 'Résultats enregistrés avec succès.';


                } catch (\Exception $ex) {
                    echo 'Erreur de lecture du fichier : ' . $ex->getMessage();
                }
            }
            afficherPage('templates/admin/membres/upload_results.htm');
        }
        else
            afficherPage (PAGE_ERREUR);
    }
    
    /**
     * @access public
     * @param \Base $f3
     * @return void
     */
    public function questionFausses($f3) {
        if(\Membre\Manager::instance()->getConnected()) {
            if(!\Sujet\Manager::instance()->idExiste($f3->get('PARAMS.sujet'))) {
                echo 'Adresse invalide : sujet inconnu.';
            }
            else {
                $f3->set('questions', \Sujet\Manager::instance()->getFromId($f3->get('PARAMS.sujet'))->getQuestions());
                $f3->set('resultats', \Membre\Manager::instance()->getConnected()->getAllResultats());
                afficherPage('templates/membre/questions_fausses.htm');
            }
        }
        else
            afficherPage (PAGE_ERREUR);
    }
}