<?php

namespace Membre;

/**
 * Structure accueillant les différents enregistrement de la table 'membres' contenant les différents membre inscrits au site.
 */

class Data {
    
    const CHARS_NOM = 'a-zA-Zàâäèéëêïîöôüû -';
    
    const SITUATION_PRIMANT = 1;
    const SITUATION_DOUBLANT = 2;
    const SITUATION_TRIPLANT= 3;
    const SITUATION_TUTEUR = 4;
    
    const STATUT_NON_VALIDE = -1;
    
    public static function getSituationName($situation_id) {
        switch($situation_id) {
            case self::SITUATION_PRIMANT:
                return "Primant";
            case self::SITUATION_DOUBLANT:
                return "Doublant";
            case self::SITUATION_TRIPLANT:
                return "Triplant";
            case self::SITUATION_TUTEUR:
                return "Membre du Tutorat";
            default:
                throw new \Exception('Situation "' . $situation_id . '" inconnu.');
        }
    }
    
    public static function getSituationId($situation_name) {
        switch(strtolower($situation_name)) {
            case "primant":
                return self::SITUATION_PRIMANT;
            case "doublant":
                return self::SITUATION_DOUBLANT;
            case "triplant":
                return self::SITUATION_TRIPLANT;
            case "membre du tutorat":
                return self::SITUATION_TUTEUR;
            default:
                throw new \Exception('SITUATION "' . $situation_name . '" inconnu.');
        }
    }
    
    const SITE_ORSAY = 1;
    const SITE_CHATENAY = 2;
    
    public static function getSiteNom($site_id) {
        switch ($site_id) {
            case self::SITE_ORSAY:
                return 'Orsay';
            case self::SITE_CHATENAY:
                return 'Châtenay';
            default:
                throw new \Exception('SITE "' . $site_id . '" inconnu.');
        }
    }
    
    public static function getSiteId($site_name) {
        switch (strtolower($site_name)) {
            case 'orsay':
                return self::SITE_ORSAY;
            case 'châtenay' || 'chatenay':
                return self::SITE_CHATENAY;
            default:
                throw new \Exception('SITE "' . $site_name . '" inconnu.');
        }
    }
    
    
    /**
     * Longueur du mdp aléatoire
     */
    const MDP_ALEAT_LONGUEUR = 8;
    const MDP_ALEAT_POSSIBLE = 'abcdefghijklmnopqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

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
        if(preg_match('#^[A-Za-z0-9]*$#', $pseudo)) {
            $this->pseudo = $pseudo;
        }
        else {
            throw new \Exception('pseudo invalide : '.$pseudo);
        }
    }

    /**
     * Génère un pseudo à partir des nom est prénom.
     * On enlève les accents et les tirets.
     * Ici ce sera Jack Dutronc => dutronj
     * @access public
     */

    public  function setPseudoFromNom() {
        $pseudo = strtolower($this->nom) . strtolower($this->prenom)[0];
        $pseudo = preg_replace('#-#', '', $pseudo);
        $pseudo = preg_replace('#[éèêë]#', 'e', $pseudo);
        $pseudo = preg_replace('#[àâä]#', 'a', $pseudo);
        $pseudo = preg_replace('#[ûü]#', 'u', $pseudo);
        $pseudo = preg_replace('#[îï]#', 'i', $pseudo);
        $pseudo = preg_replace('#[ôö]#', 'o', $pseudo);
        $pseudo = preg_replace('# #', '', $pseudo);
        $this->setPseudo($pseudo);
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
        if(is_numeric($matieres)) {
            $this->matieres = $matieres;
        }
        elseif(is_string($matieres) && $matieres != '') {
            $f3 = \Base::instance();
            $this->setMatieres(0);
            preg_replace('# #', '', $matieres);
            $matieres_array = explode(',', $matieres);
            foreach($matieres_array as $matiere) {
                if(in_array($matiere, $f3->get('matieres'))) {
                    $num_matiere = array_flip($f3->get('matieres'))[$matiere];
                    $this->setMatiere($num_matiere, 1);
                }
                else {
                    throw new \Exception('Matière invalide : ' . $matiere);
                }
            }
        }
        elseif ($matieres == '') {
            $this->setMatieres(0);
        }
        else {
            throw new \Exception('matiere invalide : ' . $matieres);
        }
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
     * Génère un mot de passe avec self::MDP_ALEAT_LONGUEUR caractères pris au hasard dans self::MDP_ALEAT_POSSIBLE.
     * @access public
     * @return string Le mot de passe généré.
     */
    public function genererMdp() {
        $mdp = "";
        for ($i = 0; $i < self::MDP_ALEAT_LONGUEUR; $i++) {
            $possible = self::MDP_ALEAT_POSSIBLE;
            $mdp .= $possible[rand(0, strlen($possible)-1)];
        }
        $this->mdp = sha1($mdp);
        return $mdp;
    }


    /**
     * @access public
     * @return string
     */

    public  function getNom() {
        return $this->nom;
    }
    
    /**
     * 
     * @return string le nom complet du membre (ex: Charles Dupont)
     */
    public function getNomComplet() {
        return $this->prenom . ' ' . $this->nom;
    }


    /**
     * Vérifie la validité du nom et s'il est valide, hydrate le membre courant.
     * @access public
     * @throws Exception
     * @param string $nom 
     */

    public  function setNom($nom) {
        if(preg_match('#^[' . self::CHARS_NOM . ']{2,30}$#', $nom)) {
            $this->nom = $nom;
        }
        else {
            throw new \Exception('Nom invalide : "' . $nom . '". Charactères acceptés : ' . self::CHARS_NOM . ', Entre 2 et 30 charactères.');
        }
    }


    /**
     * @access public
     * @return string
     */

    public  function getPrenom() {
        return $this->prenom;
    }


    /**
     * Vérifie la validité du prénom et s'il est valide, hydrate le membre courant.
     * @access public
     * @throws Exception
     * @param string $prenom 
     */

    public  function setPrenom($prenom) {
        if(preg_match('#^[' . self::CHARS_NOM . ']{2,30}$#', $prenom)) {
            $this->prenom = $prenom;
        }
        else {
            throw new \Exception('Prénom invalide : "' . $prenom . '". Charactères acceptés : ' . self::CHARS_NOM . ', Entre 2 et 30 charactères.');
        }
    }


    /**
     * @access public
     * @return string
     */

    public  function getSite() {
        return self::getSiteNom($this->site);
    }


    /**
     * Vérifie la validité du site et s'il est valide, hydrate le membre courant.
     * @access public
     * @throws Exception
     * @param string $site 
     */

    public final  function setSite($site) {
        if(is_numeric($site)) {
            $this->site = $site;
        }
        elseif (is_string($site)) {
            $this->site = self::getSiteId($site);
        }
        else {
            throw new \Exception('Site invalide : ' . $site . '. Mettez Chatenay ou Orsay');
        }
    }


    /**
     * @access public
     * @return string
     */

    public  function getSituation() {
        return self::getSituationName($this->situation);
    }
    
    /**
     * Permet de déterminer si le membre courant est un membre du tutorat (non étudiant en P1).
     * @return bool True si le membre courant est un membre du tutorat, d'après self::SITUATION_TUTEUR.
     */
    public function estTuteur() {
        return $this->situation == self::SITUATION_TUTEUR;
    }

    /**
     * @access public
     * @param int|string $situation 
     */

    public  function setSituation($situation) {
        if(is_numeric($situation)) {
            $this->situation = $situation;
        }
        elseif(is_string($situation)) {
            $this->setSituation(self::getSituationId($situation));
        }
        else {
            throw new \Exception('Situation invalide : ' . $situation);
        }
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
        if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->email = $email;
        }
        else {
            throw new \Exception('Email invalide : ' . $email);
        }
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
        if(preg_match('#^0\d([-. ]?\d{2}){4}$#', $portable) OR $portable == '') {
            $this->portable = $portable;
        }
        else {
            throw new \Exception('Numéro de téléphone invalide : ' . $portable . '. Format attendu : 0123456789');
        }
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
     * Permet de déterminer si le membre courant a un compte qui à été valider.
     * @return bool True si le membre courant a un compte valide.
     */
    public function estValide() {
        return $this->statut != self::STATUT_NON_VALIDE;
    }


    /**
     * @access public
     * @param int $statut
     */

    public final  function setStatut($statut) {
        if(is_numeric($statut)) {
            $this->statut = $statut;
        }
        elseif(is_string($statut)) {
            $this->setStatut(\Statut\Manager::instance()->getFromName($statut)->getId());
        }
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
    
    /**
     * Génère un mot de passe pour l'utilisateur et lui envoie par mail avec son identifiant.
     * @access public
     * @return void
     */
    public function sendEmailAndGenerateMdp() {
        ini_set("SMTP", "smtp.tsps.fr");
        ini_set("sendmail_from", "postmaster@tsps.fr");

        $nouveau_mdp = $this->genererMdp();
        $header ='From: postmaster@tsps.fr'."\n"
                .'Reply-To: postmaster@tsps.fr'."\n"
                .'Content-Type: text/html; charset="utf-8"'."\n"
                .'Content-Transfer-Encoding: 8bit';
        $content = 'Bonjour,<br/>'
                    . 'Voici vos nouvelles informations de connexion pour le site du tutorat de médecine du '
                    . 'Kremlin-Bicêtre.<br/>'
                    . '<strong>Identifiant : ' . $this->getPseudo() . '</strong><br/>'
                    . '<strong>Mot de passe : ' . $nouveau_mdp . '</strong><br/>';
        $content .= 'Nous vous souhaitons une bonne utilisation du site www.tsps.fr<br/>'
                    . 'Pensez à modifier votre mot de passe à votre première connexion.<br/>'
                    . 'A bientôt !<br/>'
                    . 'L\'équipe du tutorat.';
        if(!mail($this->email, 'Vos identifiant pour le tutorat KB', $content, $header)) {
            \Msg::instance()->add(3, 'Erreur lors de l\'envoi de l\'email à '.$this->getPrenom().' '.$this->getNom().'. Veuillez vérifier son adresse email.');
        }
    }
}