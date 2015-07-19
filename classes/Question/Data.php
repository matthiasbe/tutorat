<?php

namespace Question;

/*
 * Classe contenant les données d'une question
 */

class Data {
    protected $id;
    protected $numero_question;
    protected $id_sujet;
    protected $matiere;
    protected $auteurs;
    protected $question;
    protected $exercice;
    protected $enonce_sup;
    protected $enonce_inf;
    protected $image;
    protected $largeur_image;
    protected $reponse;
    protected $items;
    protected $date;
    
    public function __construct($donnees) {
        if(!is_array($donnees)) trigger_error('Pour créer une classe Question\Data, un array doit être passé en paramètre. Ici on a donné : ' . var_dump($donnees));
        $this->hydrate($donnees);
    }
    
    public function hydrate($donnees) {
        $this->initItems();
        foreach($donnees as $cle=>$valeur) {
            $resultats = array();
            if(preg_match('#^(item|correction)([12345])$#', $cle, $resultats)) {
                $setter = 'set' . ucfirst($resultats[1]); // Vaut setCorrection ou setItem
                $this->items[$resultats[2]]->$setter($valeur);
            }
            else
                $this->$cle = $valeur;
        }
    }
    
    public function initItems() {
        $this->items = array();
        $this->items[1] = new Item;
        $this->items[2] = new Item;
        $this->items[3] = new Item;
        $this->items[4] = new Item;
        $this->items[5] = new Item;
    }
    
    public function getNumero_question() {
        return $this->numero_question;
    }
    
    public function decrNumQuestion() {
        $this->numero_question--;
    }
    
    public function incrNumQuestion() {
        $this->numero_question++;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getId_sujet() {
        return $this->id_sujet;
    }
    
    public function getMatiere() {
        return $this->matiere;
    }
    
    public function setMatiere($matiere) {
        $this->matiere = $matiere;
    }
    
    public function getAuteurs() {
        return $this->auteurs;
    }
    
    public function setAuteurs($auteurs) {
        $this->auteurs = $auteurs;
    }
    
    public function getEnonce_sup() {
        return $this->enonce_sup;
    }
    
    public function getEnonce_inf() {
        return $this->enonce_inf;
    }
    
    public function getQuestion() {
        return $this->question;
    }
    
    public function getExercice() {
        return $this->exercice;
    }
    
    public function getItem($indice) {
        return $this->items[$indice]->getItem();
    }
    
    public function getCorrection($indice) {
        return $this->items[$indice]->getCorrection();
    }
    
    public function getLargeur_image() {
        return $this->largeur_image;
    }
    
    public function getImage() {
        return $this->image;
    }
    
    public function getDate() {
        return $this->date;
    }
    
    /**
     * Renvoie un entier sur 5 bits contenant les réponses au 5 items
     * @access public
     * @return int Entier sur 5 bit.
     */
    public function getReponses() {
        return $this->reponse;
    }
    
    /**
     * Renvoie une chaine de 5 caractères indiquant les réponses en lettre. Ex : VVFVF pour reponse = 25.
     * @access public
     * @return string La réponse en 5 lettres majuscule.
     */
    public function getReponsesEnLettres() {
        $lettres = '';
        for($i = 1; $i <= 5; $i++) {
            $lettres .= $this->getReponse($i) ? 'V' : 'F';
        }
        return $lettres;
    }
    
    /**
     * Renvoi la réponse à l'item demandé.
     * @param int $indice Compris entre 1 et 5.
     * @return bool True pour vrai, false pour une réponse fausse.
     */
    public function getReponse($indice) {
        return getBit($this->getReponses(), $indice);
    }
    
    /**
     * remplit un Mapper avec les champs de la question courante
     * @access public
     * @param \DB\SQL\Mapper $mapper le mapper à remplir.
     * @return void
     */
    public function remplirMapper(\DB\SQL\Mapper $mapper) {
        foreach ($this as $key=>$value) {
            if($key != 'items')
                $mapper->$key = $value;
            else {
                foreach($this->items as $num_item=>$item) {
                    $nom_item = 'item' . $num_item;
                    $correction = 'correction' . $num_item;
                    $mapper->$nom_item = $item->getItem();
                    $mapper->$correction = $item->getCorrection();
                }
            }
        }
    }
    
    /**
     * Renvoi true si la question est rattachée à un sujet
     * @access public
     * @return bool
     */
    public function estRattachee() {
        return $this->id_sujet > 0;
    }
    
    /**
     * Renvoi true si la question est indépendante et non banquée
     * @access public
     * @return bool
     */
    public function estIndependante() {
        return $this->id_sujet == 0;
    }
    
    /**
     * Renvoi true si la question est banquée.
     * @access public
     * @return bool
     */
    public function estBanquee() {
        return $this->id_sujet == -1;
    }
    
    /**
     * Rattache la question à un sujet. Incrémente également le nombre de questions du sujet sans l'enregistrer.
     * Penser à appeler \Sujet\Manager::instance()->update($sujet) pour enregistrer.
     * @access public
     * @param \Sujet\Data $sujet
     * @return void
     */
    public function rattacher(\Sujet\Data $sujet) {
        /* On met à jour le sujet */
        $sujet->incrNbrQuestions();
        
        /* On mes à jour la question */
        $this->id_sujet = $sujet->getId();
        $this->matiere = $sujet->getMatiere();
        $this->numero_question = $sujet->getNombre_questions();
    }
    
    /**
     * Détache la question du sujet (met sont id_sujet à 0). Décrémente également le nombre de questions du sujet sans l'enregistrer.
     * Penser à appeler \Sujet\Manager::instance()->update($sujet) pour enregistrer.
     * @access public
     * @param \Sujet\Data $sujet
     * @return void
     */
    public function detacher($sujet) {
        /* On met à jour le sujet */
        $sujet->decrNbrQuestions();
        
        /* On mes à jour la question */
        $this->id_sujet = 0;
    }
    
    /**
     * Banque la question. (met sont id_sujet à -1).
     * Renvoie une erreur si la question n'est pas indépendante.
     * @access public
     * @return void
     */
    public function banquer() {
        if($this->estIndependante()) {
            $this->id_sujet = -1;
        }
        else {
            trigger_error('Une question rattachée ou déjà banquée ne peut pas être banquée.');
        }
    }
    
    /**
     * Retire la question de la banque de QCMS en la rendant indépendante. (met sont id_sujet à 0).
     * Renvoie une erreur si la question n'est pas banquée.
     * @access public
     * @return void
     */
    public function debanquer() {
        if($this->estBanquee()) {
            $this->id_sujet = 0;
        }
        else {
            trigger_error('Une question non banquée ne peut pas être débanquée.');
        }
    }
    
    /**
     * Complète le champ auteurs de la question courante avec le champ auteurs du sujet rattaché s'il y en a un.
     * Sinon on met l'utilisateur connecté.
     * @access public
     * @return void
     */
    public function determinerAuteurs() {
        if($this->getId()) {
            $this->setAuteurs(Manager::instance()->getFromId($this->getId())->getAuteurs());
        }
        else {
            $this->setAuteurs(\Base::instance()->get('SESSION.user')->getId());
        }
    }
}