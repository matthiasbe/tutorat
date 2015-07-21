<?php

namespace Membre;

/**
 * Structure accueillant les différents enregistrement de la table 'membres' contenant les différents membre inscrits au site.
 */

class Data {
    
    const SITUATION_PRIMANT = 1;
    const SITUATION_DOUBLANT = 2;
    const SITUATION_TRIPLANT= 3;
    const SITUATION_TUTEUR = 4;

    /**
     * @var int
     * @access protected
     */
    protected  $id;

    /**
     * @var string
     * @access protected
     */
    protected  $pseudo;

    /**
     * @var int
     * @access protected
     */
    protected  $matieres;

    /**
     * @var string
     * @access protected
     */
    protected  $mdp;

    /**
     * @var string
     * @access protected
     */
    protected  $nom;

    /**
     * @var string
     * @access protected
     */
    protected  $prenom;

    /**
     * Deux valeurs possible: Châtenay ou Orsay
     * @var string
     * @access protected
     */
    protected  $site;

    /**
     * Trois valeurs : primant, doublant ou triplant
     * @var string
     * @access protected
     */
    protected  $situation;

    /**
     * @var string
     * @access protected
     */
    protected  $email;

    /**
     * @var string
     * @access protected
     */
    protected  $portable;

    /**
     * Niveau de  permission du membre.
     * @var int
     * @access protected
     */
    protected  $statut;

    /**
     * Hydrate la classe avec un tableau de valeurs.
     * @access public
     * @param array $donnees Doit contenir au moins un champ id ou pseudo. Id est prioritaire.
     */
    public function __construct($donnees) {
        if(!is_array($donnees)) {
            trigger_error('Pour créer une classe Membre\Data, un array doit être passé en paramètre.');
        }
        $this->hydrate($donnees);
    }

    /**
     * @access public
     * @param Array $donnees
     */
    public function hydrate($donnees) {
        foreach($donnees as $cle=>$valeur) {
            $setter = 'set' . ucfirst($cle);
            if(method_exists('\Membre\Data', $setter)) {
                $this->$setter($valeur);
            }
        }
    }


    /**
     * @access public
     * @return int
     */

    public  function getId() {
        return $this->id;
    }


    /**
     * @access public
     * @param int $id 
     */

    public  function setId($id) {
        $this->id = $id;
    }


    /**
     * @access public
     * @return string
     */

    public  function getPseudo() {
        return $this->pseudo;
    }


    /**
     * 	
     * @access public
     * @param string $pseudo 
     */

    public  function setPseudo($pseudo) {
        $this->pseudo = $pseudo;
    }


    /**
     * @access public
     * @return int Un nombre binaire contenant les matieres attitrées au membre.
     */

    public  function getMatieres() {
        return $this->matieres;
    }


    /**
     * @access public
     * @param int $num_matiere Le numéro de la matière, cf finchier de configuration.
     * @return bool True si la matiere donnée est attitrée au membre.
     */

    public  function getMatiere($num_matiere) {
        return getBit($this->matieres, $num_matiere);
    }

    /**
     * Détermine si le membre courant gère une matière donnée.
     * Alias de getMatière.
     * @access public
     * @param int $num_matiere Le numéro de la matière, cf finchier de configuration.
     * @return bool True si la matiere donnée est gérée par le membre.
     */

    public  function gereMatiere($num_matiere) {
        return $this->getMatiere($num_matiere);
    }


    /**
     * @access public
     * @param int $matieres 
     */

    public  function setMatieres($matieres) {
        $this->matieres = $matieres;
    }


    /**
     * @access public
     * @param int $num 
     * @param bool $value 
     */

    public  function setMatiere($num, $value) {
        $matieres = $this->getMatieres();
        if($matieres != NULL) {
            $this->setMatieres(setBit($matieres, $num, $value));
        }
        else {
            $this->setMatieres(pow(2, $num-1));
        }
    }


    /**
     * @access public
     * @param string $mdp 
     * @return void
     */

    public  function setMdp($mdp) {
        $this->mdp = $mdp;
    }


    /**
     * @access public
     * @return string
     */

    public  function getNom() {
        return $this->nom;
    }


    /**
     * @access public
     * @param string $nom 
     */

    public  function setNom($nom) {
        $this->nom = $nom;
    }


    /**
     * @access public
     * @return string
     */

    public  function getPrenom() {
        return $this->prenom;
    }


    /**
     * @access public
     * @param string $prenom 
     */

    public  function setPrenom($prenom) {
        $this->prenom = $prenom;
    }


    /**
     * @access public
     * @return string
     */

    public  function getSite() {
        return $this->site;
    }


    /**
     * @access public
     * @param string $site 
     */

    public final  function setSite($site) {
        $this->site = $site;
    }


    /**
     * @access public
     * @return string
     */

    public  function getSituation() {
        switch($this->situation) {
            case self::SITUATION_PRIMANT:
                return "Primant";
            case self::SITUATION_DOUBLANT:
                return "Doublant";
            case self::SITUATION_TRIPLANT:
                return "Triplant";
            case self::SITUATION_TUTEUR:
                return "Membre du Tutorat";
        }
    }
    
    public function estTuteur() {
        return $this->situation == self::SITUATION_TUTEUR;
    }

    /**
     * @access public
     * @param string $situation 
     */

    public  function setSituation($situation) {
        $this->situation = $situation;
    }


    /**
     * @access public
     * @return string
     */

    public  function getEmail() {
        return $this->email;
    }


    /**
     * @access public
     * @param string $email 
     */

    public final  function setEmail($email) {
        $this->email = $email;
    }


    /**
     * @access public
     * @return string
     */

    public  function getPortable() {
        return $this->portable;
    }


    /**
     * @access public
     * @param string $portable
     */

    public  function setPortable($portable) {
        $this->portable = $portable;
    }


    /**
     * @access public
     * @return int
     */

    public  function getStatut() {
        return $this->statut;
    }


    /**
     * @access public
     * @return \Statut\Data
     */

    public  function getStatutObject() {
        return \Statut\Manager::instance()->getFromId($this->statut);
    }


    /**
     * @access public
     * @param int $statut
     */

    public final  function setStatut($statut) {
        $this->statut = $statut;
    }


    /**
     * Renvoie true si le mot de passe en paramètre est égale au mdp réèl, une fois encodé.
     * @access public
     * @param string $mdp 
     * @return bool
     */

    public  function testMdp($mdp) {
        return $this->mdp == sha1($mdp);
    }
    
    /**
     * Renvoie tous les résultats du membre courant.
     * @access public
     * @return Array un tableau contenant les \Resultats\Data du membre courant
     */
    public function getAllResultats() {
        return \Resultat\Manager::instance()->getFromMembre($this->getId());
    }
    
    /**
     * Liste les matières attitrées dans une liste (entourés de balises <li>) avec un lien de suppression
     * @access public
     * @return string Le code HTML de la liste.
     */
    public function listerMatieres() {
        $f3 = \Base::instance();
        $retour = '<ul>';
        foreach ($f3->get('matieres') as $num_matiere=>$matiere) {
            if($this->getMatiere($num_matiere)) {
                $retour .= '<li>'. $matiere
                        .' <img class="bouton" onclick="supprimerMatiere(\'' . $num_matiere
                        .'\')" src="' . $f3->get('root') . 'files/images/delete.png" alt="Supprimer" /></li>';
            }
        }
        return $retour . '</ul>';
    }
    
    
    /**
     * Liste les matières non attitrées entourées de balises <option> pour un bouton select (menu déroulant);
     * @access public
     * @return void
     */
    public function getMatieresNonSuiviesInArray() {
        $f3 = \Base::instance();
        $matieres = array();
        foreach($f3->get('matieres') as $key=>$value) {
            if(!$this->getMatiere($key)) {
                $matieres[$key] = $value;
            }
        }
        return $matieres;
    }
    
    /**
     * remplit un Mapper avec les champs du membre
     * @access public
     * @param \DB\SQL\Mapper $mapper
     * @return void
     */
    public function remplirMapper(\DB\SQL\Mapper $mapper) {
        foreach ($this as $key=>$value) {
            $mapper->$key = $value;
        }
    }
    
    /**
     * Permet d'obtenir un tableau des matières suivies par le membre courant.
     * Les clés du tableau correspondent aux id des matières.
     * @access public
     * @return array L'ensemble des matières attribuées au membre.
     */
    public function getMatieresInArray() {
        $f3 = \Base::instance();
        $tableau_mat = array();
        foreach($f3->get('matieres') as $cle=>$matiere) {
            if($this->getMatiere($cle)) {
                $tableau_mat[$cle] = $matiere;
            }
        }
        return $tableau_mat;
    }
    
    /**
     * Permet de déterminer si le membre courant est l'auteur d'une question ou d'un sujet.
     * @access public
     * @param Mixed $sujet_ou_question Le sujet ou la question dont on veut vérifier l'auteur.
     * @return bool True si le membre courant est l'auteur.
     */
    public function isAuteur($sujet_ou_question) {
        return $this->getId() == $sujet_ou_question->getAuteurs();
    }

    
    /**
     * @access public
     * @return array Un tableau contenant les \Question\Data du membre
     */
    public function getQuestions() {
        return \Question\Manager::instance()->getFromMembre($this);
    }

    
    /**
     * @access public
     * @return array Un tableau contenant les \Sujet\Data du membre
     */
    public function getSujets() {
        return \Sujets\Manager::instance()->getFromMembre($this);
    }
}