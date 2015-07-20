<?php

namespace Sujet;

/* 
 * Classe appelée pour afficher les pages rélatives aux questions
 */

class View {
    
    /**
     * Affiche le sujet avec ses questions avec édition.
     * PARAMS.id : id du sujet à afficher
     * @access public
     * @param Base $f3
     * @return void
     */
    public function afficher($f3) {
        $this->id = $f3->get('PARAMS.id');
        
        $sujet = Manager::instance()->getFromId($f3->get('PARAMS.id'));
        
        if(CIA(EDIT_SUJET, 0, $sujet)) {
            $f3->set('sujet', $sujet);
            $f3->set('questions', $sujet->getQuestions());

            afficherPage('templates/sujets/sujet.htm');
        }
        else
            afficherPage (PAGE_ERREUR);
    }
    
    /**
     * Affiche la liste des sujets
     * @access public
     * @param Base $f3
     * @return void
     */
    public function liste($f3) {
        if(CIA(SEE_SUJETS, 0)) {
            $tableau_matiere = \Membre\Manager::instance()->getConnected()->getMatieresInArray();
            $sujets =  Manager::instance()->getFromMatiere($tableau_matiere);
            $f3->set('result', $sujets);
            $f3->set('matiereAffichee', 'all');
            $f3->set('liste_sujets_active', 1);
            $f3->set('titre', 'Liste de tous les sujets');

            afficherPage('templates/sujets/liste.htm');
        }
        else
            afficherPage (PAGE_ERREUR);
    }
    
    public function mesSujets($f3) {
        if(CIA(ADD_SUJET)) {
            $f3->set('result', Manager::instance()->getMesSujets());
            $f3->set('matiereAffichee', 'all');
            $f3->set('titre', 'Liste de mes sujets');

            afficherPage('templates/sujets/liste.htm');
        }
        else
            afficherPage (PAGE_ERREUR);
    }
    
    public function ajouter() {
        if(CIA(ADD_SUJET, 0) && \Membre\Manager::instance()->getConnected()->getMatieres()) {
            afficherPage('templates/sujets/ajouter.htm');
        }
        else
            afficherPage (PAGE_ERREUR);
    }
    
    /**
     * Affiche le sujet au format PDF
     * @access public
     * @param Base $f3
     * @return void
     */
    function renduPdf($f3) {
        if(CIA(SEE_SUJETS_ET_COR)) {
            $pdf = new \Pdf;
            $pdf->rendu(Manager::instance()->getFromId($f3->get('PARAMS.id')));
            echo \Template::instance()->render($pdf->rendu($f3, $this));
        }
        else {
            echo ERREUR;
        }
    }
    
    function renduCorrige($f3) {
        if(CIA(SEE_SUJETS_ET_COR)) {
            $pdf = new \PdfCorrige;
            $pdf->corrige(Manager::instance()->getFromId($f3->get('PARAMS.id')));
            echo \Template::instance()->render($pdf->rendu($f3, $this));
        }
        else {
            echo ERREUR;
        }
    }
    
    /**
     * Reception du formulaire d'ajout d'un sujet.
     * POST contient les différentes infos du sujet à ajouter.
     * @access public
     * @param Base $f3 L'instance de la classe de base de FatFreeFramework. (passée en argument automatiquement par f3)
     * @return void
     */
    public function receptionAjout($f3) {
        if(CIA(ADD_SUJET)) {
            $sujet = new Data(array(
                    'matiere' => $f3->get('POST.matiere'),
                    'numero_cb' => $f3->get('POST.numero_cb'),
                    'nombre_questions' => 0,
                    'date' => $f3->get('POST.date'),
                    'notions' => $f3->get('POST.notions'),
                    'auteurs' => $f3->get('SESSION.user')->getId()
                    ));
            Manager::instance()->add($sujet);
            $f3->reroute('/sujets');
        }
        else {
            echo ERREUR;
        }
    }
    
    /**
     * Reception du formulaire de suppression d'un sujet.
     * PARAMS.id contient l'id du sujet à supprimer.
     * @access public
     * @param Base $f3 L'instance de la classe de base de FatFreeFramework. (passée en argument automatiquement par f3)
     * @return void
     */
    public function receptionSupprimer($f3) {
        if(CIA(DELETE_SUJET)) {
            $s_manager = Manager::instance();
            $sujet = $s_manager->getFromId($f3->get('PARAMS.id'));
            if($sujet != NULL) {
                $s_manager->delete($sujet);
            }
            else {
                trigger_error('Impossible de supprimer le sujet : id inconnu. id : ' . $f3->get('PARAMS.id'));
            }
        }
        else {
            echo ERREUR;
        }
    }
    
    /**
     * Affiches les sujet et corrections qui ont déjà été distribués.
     * @access public
     * @param \Base $f3
     * @return void
     */
    public function archives($f3) {
        if(CIA(SEE_SUJETS_ET_COR)) {
        $sujets = Manager::instance()->getArchives();
        $f3->set('sujets', $sujets);
        afficherPage('templates/sujets/archives.htm');
        }
        else
            afficherPage (PAGE_ERREUR);
    }
}