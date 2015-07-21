<?php
/**
 * Remarque : la génération des images PNG est très longues. JPG quasimment instantanée
 */

require_once 'tcpdf/tcpdf.php';

class Pdf extends TCPDF {

    /**
     * @var string Le nom de la matière du sujet.
     * @access private
     */
    private $nomMatiere;
    
    /**
     * @var string Le nom abrégé de la matière du sujet.
     * @access private
     */
    private $nomAbrege;
    
    /**
     * @var \Sujet\Data Le sujet dont on fait le rendu.
     * @access private
     */
    private $sujet;
    
    /**
     * @var int Numéro de l'exercice courant. S'incrémente à chaque rendu d'exercice
     * @access private
     */
    private $num_exercice = 0;

    /**
     * Fonction de génération du PDF -> cette fonction appelle les autres
     * @access public
     * @param \Sujet\Data $sujet
     * @return String
     */

    public function rendu(\Sujet\Data $sujet) {
        $f3 = \Base::instance();
        $this->sujet = $sujet;
        $questions = $sujet->getQuestions();

        $this->nomAbrege = 'CB' . $sujet->getNumero_cb();
        $this->numMatiere = $sujet->getMatiere();
        $this->nomMatiere = $f3->get('matieres')[$sujet->getMatiere()];
        
        $this->initialisation();

        $this->premierePage();
        foreach ($questions as $question) {
            $this->question($question);
        }

        return $this->Output('Sujet.pdf', 'I');
    }
    
    /**
     * Initialise les variable du pdf (auteur, nom du fichier, etc...)
     * @access public
     * @return void
     */
    public function initialisation() {
        $this->setImageScale(1.5);
    }

    /**
     * Met en page le Header de chaque page (logo, un ligne de séparation etc..)
     * @access public
     * @return void
     */
    public function Header() {
        $this->SetTopMargin(35); // Ajoute un espace pour l'en-tête

        $image_file = realpath('') . '/files/images/logo_tsps.jpg';
        $this->image($image_file,0,5,60,'','JPG','','T',false,'','L');
        $this->SetFont('helvetica', 'B', 8);
        $this->writeHTMLCell('','',180,5,'Tutorat<br/>Santé<br/>Paris-Sud', 'L');
        $this->writeHTMLCell('','',168,5,$this->nomAbrege . '<br/>' . $this->nomMatiere);
    }

    /**
     * Met en page le Footer de chaque page (numéro de page etc ...)
     * @access public
     * @return void
     */
    public function Footer() {

        $this->setFooterMargin(23);
        $this->SetAutoPageBreak(true, 40);

        $this->writeHTML('<span align="center">Ce document est réalisé sous l’entière responsabilité du TSPS et ne constitue en aucun cas une <br/>référence opposable aux épreuves.</span>');
        //$this->writeHTMLCell($w, $h, $x, $y, $html, $border, $ln, $fill, $reseth, $align);
    }

    /**
     * Met en page la premiere page.
     * @access public
     * @return void
     */
    public function premierePage() {
        $this->AddPage();

        $this->writeHTMLCell('','',0,45,
            '<h2 align="center">Concours blanc ' . $this->nomMatiere . ' N°' . $this->sujet->getNumero_cb() . '</h2>');
        $image_file = realpath('') . '/files/images/logo.jpg';
        $this->image($image_file,30,65,130,'','JPG','','M',false,'','M');

        $this->writeHTMLCell('','',0,205,
            '<h2 align="center">SUJET</h2>'
            . '<h2 align="center">Notions traitées : ' . $this->sujet->getNotions() . '</h2>'
            . '<h2 align="center">' . $this->sujet->getDate() . '</h2></div>');

        $this->AddPage();
    }

    /**
     * Met en page une question
     * @access public
     * @param \Question\Data $question La question à afficher.
     * @return void
     */
    function question(\Question\Data $question) {
        $texte = '';
        
        //Exercice
        if($question->getExercice()) {
            $this->num_exercice++;
            $texte .= '<br/><h3>Exercice ' . $this->num_exercice . ' : </h3>';
        }
        //Enoncé sup
        $texte .= '<p>' . $question->getEnonce_sup() . '</p>';

        // On concatène la question
        $texte .= '<br/><h4>QCM ' . $question->getNumero_question() . '. ' . $question->getQuestion() . ' :</h4><br/>';
        
        //Enoncé inf
        $texte .= '<p>' . $question->getEnonce_inf() . '</p>';

        // Calcul des dimensions de l'image
        $attribut_largeur =  'width="' . $question->getLargeur_image() . '" ';

        // On concatène l'image
        if($question->getImage() != '') {
            $texte .= '<p><img src="' . \ImgMng::getInstance()->getFolder() . '/' . $question->getImage(). '" ' . $attribut_largeur . '/></p>';

        }
        // On affiche les cinq items
        for($i=1; $i<=5;$i++)
            $texte .= '<p>' . ($i + 5*($question->getNumero_question()-1)) . ') '. $question->getItem($i) . '</p>';
        
        $this->writeHTML($texte);
    }
}
