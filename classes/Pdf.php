<?php

require_once 'tcpdf/tcpdf.php';

class Pdf extends TCPDF {
    
        private $nomMatiere;
        private $numMatiere;
        private $nomAbrege;
        private $f3;
        private $sujet;
        private $questions;
        
        /* Fonction de génération du PDF -> cette fonction appelle les autres */
        public function rendu($f3, $sujet) {
            $this->f3 = $f3;
            $this->sujet = $sujet->getSujet($f3);
            $this->questions = $sujet->getQuestions($f3);
            
            $this->nomAbrege = 'CB' . $this->sujet['numero_cb'];
            $this->numMatiere = $this->sujet['matiere'];
            $this->nomMatiere = $f3->get('matieres')[$this->sujet['matiere']];
            
            $this->premierePage();
            foreach ($this->questions as $question) {
                $this->question($question);
            }
            
            return $this->Output('Sujet.pdf', 'I');
        }
        
        public function Header() {
            $this->SetTopMargin(28); // Ajoute un espace pour l'en-tête
            
            $image_file = realpath('') . '/files/images/logo_tsps.png';
//            $this->image($image_file,0,-5,50,'','PNG','','T',false,'','L'); //Commentez cette ligne pour une génération presque instantanée du pdf
            //$this->writeHTML('<img src=' . $image_file . ' />');
            $this->SetFont('helvetica', 'B', 8);
            $this->writeHTMLCell('','',180,5,'Tutorat<br/>Santé<br/>Paris-Sud', 'L');
            $this->writeHTMLCell('','',168,5,$this->nomAbrege . '<br/>' . $this->nomMatiere);
        }
        
        public function Footer() {
            
            $this->setFooterMargin(32);
            $this->SetAutoPageBreak(true, 40);
            
            $this->writeHTML('<span align="center">Ce document est réalisé sous l’entière responsabilité du TSPS et ne constitue en aucun cas une <br/>référence opposable aux épreuves.</span>');
            //$this->writeHTMLCell($w, $h, $x, $y, $html, $border, $ln, $fill, $reseth, $align);
        }
        
        public function premierePage() {
            $this->AddPage();
            
            $this->writeHTMLCell('','',0,45,
                    '<h2 align="center">Concours blanc ' . $this->nomMatiere . ' N°' . $this->sujet['numero_cb'] . '</h2>');
            $image_file = realpath('') . '/files/images/logo.jpg';
            $this->image($image_file,30,65,130,'','JPG','','M',false,'','M');

            $this->writeHTMLCell('','',0,205,
                    '<h2 align="center">SUJET</h2>'
                    . '<h2 align="center">Notions traitées : ' . $this->sujet['notions'] . '</h2>'
                    . '<h2 align="center">' . $this->sujet['date'] . '</h2></div>');
            
            $this->AddPage();
        }
        
        function question($question) {
                $eqn_manager = EquationManager::getInstance();
                $texte = '';


                // On concatène la question
                $texte .= '<br/><h4>QCM ' . $question['numero_question'] . '. ' . $question['question'] . ' :</h4><br/>';

                // Calcul des dimensions de l'image
                if($question['largeur_image'])
                        $attribut_largeur = ' width="' . $question['largeur_image'] . '" ';
                else
                        $attribut_largeur = ' ';

                // On concatène l'image
                if($question['image'] != '') {
                        $texte .= '<p><img src="' . realpath('') . '/files/images/' . $question['image'] . '" ' . $attribut_largeur . '/></p>';

                }

                // On concatène les cinq items
                for($i=1; $i<=5;$i++) {
                        $texte .= '<p>' . ($i + 5*($question['numero_question']-1)) . ') '. $question['item' . $i] . '</p>';
                }

                // On affiche le tout, en remplaçant les labels par les équations
                $this->writeHTML($eqn_manager->placerEquations($texte));
        }
}
