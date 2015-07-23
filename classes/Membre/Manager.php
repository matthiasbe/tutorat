<?php

namespace Membre;

/**
 * 
 */

class Manager {
    
    /**
     * @var \DB\SQL
     */
    private $db;
    
    /**
     * @var self
     */
    private static $instance;
    
    private function __construct() {
        $this->db = \Base::instance()->get('Bdd');
    }

    /**
     * Renvoie l'instance unique de la classe manager. Il faut passer par cette appel pour se servir des fonctions de la classe.
     * Cf classe singleton.
     * @access public
     * @return self
     */
    public static function instance() {
        if(!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Met à jour un membre de la BDD. Renvoie une erreur s'il a une idée non existante.
     * @access public
     * @param Data $membre le membre à mettre à jour
     * @return void
     */
    public  function update(Data $membre) {
        if($this->idExiste($membre->getId())) {
            $membre_db = new \DB\SQL\Mapper($this->db,'membres');
            $membre_db->load(array('id=?', $membre->getId()));
            $membre->remplirMapper($membre_db);
            $membre_db->update();
        }
        else {
            trigger_error('Impossible de mettre à jour le membre : le membre a un ID invalide. id : ' . $membre->getId());
        }
    }

    /**
     * Ajout un membre à la BDD. Renvoie une erreur si il à une ID existante.
     * @access public
     * @param Data $membre le membre à mettre à jour
     * @return void
     */
    public  function add(Data $membre) {
        if(!$this->idExiste($membre->getId())) {
            $membre_db = new \DB\SQL\Mapper($this->db,'membres');
            $membre->remplirMapper($membre_db);
            $membre->setId(NULL);
            $membre_db->insert();
        }
        else {
            trigger_error('Impossible d\'ajouter ce membre : Il existe déjà. id : ' . $membre->getId());
        }
    }

    /**
     * Suppression d'un membre de la BDD. Renvoie une erreur si il à une ID existante.
     * @access public
     * @param Data $membre le membre à mettre à jour
     * @return void
     */
    public  function delete(Data $membre) {
        if($this->idExiste($membre->getId())) {
            $membre_db = new \DB\SQL\Mapper($this->db,'membres');
            $membre_db->erase(array('id=?', $membre->getId()));
        }
        else {
            trigger_error('Impossible de supprimer ce membre : ID inconnu. id : ' . $membre->getId());
        }
    }


    /**
     * @access public
     * @return Data Le membre connecté
     */

    public  function getConnected() {
        $f3 = \Base::instance();
        if($f3->exists('SESSION.user')) {
            return $f3->get('SESSION.user');
        }
        else {
            return NULL;
        }
    }

    /**
     * Deconnecte le membre actuellement connecté.
     * @access public
     * @return void
     */
    
    public function disconnect() {
        $f3 = \Base::instance();
        $f3->clear('SESSION.user');
    }
    
    /**
     * Vérifie la véracité des information (pseudo et mot de passe) et dans ce cas ajoute le membre à la session.
     * sinon lève un exception.
     * @access public
     * @param string $pseudo Le pseudo inséré (doit être sain).
     * @param string $mdp Le mot de passe inséré (doit être sain).
     * @throws \Exception
     * @return void
     */
    
    public function connect($pseudo, $mdp) {
        if(!$this->pseudoExiste($pseudo)) throw new \Exception('Cet identifiant n\'existe pas.');
        
        $membre = $this->getFromPseudo($pseudo);
        if(!$membre->estValide()) throw new \Exception('Votre inscription n\'a pas encore été validée.');
        if(!$membre->testMdp($mdp)) throw new \Exception('Mauvais mot de passe.');
        
        $f3 = \Base::instance();
        $f3->set('SESSION.user', $membre);
    }
    
    /**
     * Récupère un membre dans la BDD à partir de son id.
     * @access public
     * @param int $id_membre
     * @return \Membre\Data Null si le membre n'existe pas.
     */
    
    public function getFromId($id_membre) {
        if($this->idExiste($id_membre)) {
            $membre_array = $this->db->exec('SELECT * FROM membres WHERE id=?', $id_membre)[0];
            return new Data($membre_array);
        }
        else
            return NULL;
    }
    
    /**
     * Récupère un membre dans la BDD à partir de son pseudo.
     * @access public
     * @param string $pseudo
     * @return \Membre\Data Null si le membre n'existe pas.
     */
    
    public function getFromPseudo($pseudo) {
        if($this->pseudoExiste($pseudo)) {
            $membre_array = $this->db->exec('SELECT * FROM membres WHERE pseudo=?', $pseudo)[0];
            return new Data($membre_array);
        }
        else {
            return NULL;
        }
    }
    
    /**
     * Renvoie tous les membre de la table.
     * @access public
     * @return \Membre\Data
     */
    public function getAll() {
        $membre_array = $this->db->exec('SELECT * FROM membres');
        $membres = array();
        foreach($membre_array as $key=>$membre) {
            $membres[$key] = new Data($membre);
        }
        return $membres;
    }
    
    /**
     * @access public
     * @param int $id
     * @return bool true si un membre avec l'id donné existe et est unique
     */
    public function idExiste($id) {
        $membre = new \DB\SQL\Mapper($this->db,'membres');
        $nbr_resultats = $membre->count(array('id=?', $id));
        return ($nbr_resultats == 1);
    }
    
    /**
     * @access public
     * @param int $pseudo
     * @return bool true si un membre avec le pseudo donné existe et est unique.
     */
    public function pseudoExiste($pseudo) {
        $membre = new \DB\SQL\Mapper($this->db,'membres');
        $nbr_resultats = $membre->count(array('pseudo=?', $pseudo));
        return ($nbr_resultats == 1);
    }

    /**
     * @param int $id_membre
     * @param string $champ
     * @return bool True si le membre connecte peut modifier ce champ pour le membre dont l'id est donnée.
     */
    public function estAutoriseAModifier($id_membre, $champ) {
        $profil_du_connecte = $this->getConnected()->getId()  == $id_membre;
        $perm_own = 'EDIT_OWN_' . strtoupper($champ); // ex : EDIT_OWN_PSEUDO
        $perm_other = 'EDIT_OTHER_' . strtoupper($champ); // ex : EDIT_OTHER_PSEUDO
        return (defined($perm_other) AND CIA(constant($perm_other),0)) ||
                (defined($perm_own) AND CIA(constant($perm_own), 0) AND $profil_du_connecte);
    }
}