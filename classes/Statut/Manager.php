<?php

namespace Statut;


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
     * Enregistre un nouveau statut dans la base de données.
     * @access public
     * @param Data $statut Le statut à ajouter.
     * @return
     */

    public  function add($statut) {
        if($this->idExiste($statut->getId())) trigger_error ('Fonction ADD sur un statut existant déjà dans la BDD.');
        
        $statut_db = new \DB\SQL\Mapper($this->db, 'statuts');
        $statut->remplirMapper($statut_db);
        $statut_db->save();
    }
    
    /**
     * Met à jour le statut dans la BDD
     * @access public
     * @param Data $statut
     * @return void
     */
    public function update(Data $statut) {
        if($this->idExiste($statut->getId())) {
            $statut_db = new \DB\SQL\Mapper($this->db,'statuts');
            $statut_db->load(array('id=?', $statut->getId()));
            $statut->remplirMapper($statut_db);
            $statut_db->save();
        }
        else {
            trigger_error('Le statut a un ID invalide.');
        }
    }
    
    /**
     * Récupère un statut dans la bdd à partir de son ID.
     * @param int $id_statut
     * @return \Statut\Data Le statut ayant pour id $id_statut, NULL sinon.
     */
    
    public function getFromId($id_statut) {
        if($this->idExiste($id_statut)) {
            $res_array = $this->db->exec('SELECT * FROM statuts WHERE id=?', $id_statut)[0];
            return new Data($res_array);
        }
        else {
            return $this->getInvite();
        }
    }
    
    /**
     * Récupère le statut et le permission des personnes non connectées.
     * @access public
     * @return Data LE statut invité
     */
    function getInvite() {
        // Si le compte invité n'existe pas, on le crée sans aucune permission
        if(!$this->idExiste(ID_INVITE)) {
            $invite = new Data(array(
                'id' => ID_INVITE,
                'permissions' => 0,
                'nom' => 'Compte invité'
            ));
            Manager::instance()->add($invite);
            return $invite;
        }
        return $this->getFromId(ID_INVITE);
    }
    
    /**
     * @access public
     * @return Array Tous les statut de la BDD.
     */
    public function getAll() {
        $statuts_db = $this->db->exec('SELECT * FROM statuts');
        $statuts = array();
        foreach($statuts_db as $statut) {
            $statuts[$statut['id']] = new Data($statut);
        }
        return $statuts;
    }
    
    /**
     * Détermine si un statut existe ou non dans la BDD.
     * @access public
     * @param int $id
     * @return bool True si un statut avec l'id donné existe et est unique
     */
    public function idExiste($id) {
        $statut = new \DB\SQL\Mapper($this->db,'statuts');
        $nombre_statuts = $statut->count(array('id=?', $id));
        return ($nombre_statuts == 1);
    }
    
    /**
     * Renvoie un tableau associant les numéros des permissions à leur description.
     * @access public
     * @return array
     */
    public function getTableauPermission() {
        for($i = 1; $i <= NBR_PERMISSIONS; $i++) {
            $tableau[$i] = $this->getPermDescription($i);
        }
        return $tableau;
    }
    
    /**
     * Renvoie la description d'une permission passée en argument.
     * @access public
     * @param int $perm La permission dont on veut la description.
     * @return string La description de la permission.
     */
    public function getPermDescription($perm) {
        switch($perm) {
            case EDIT_OTHER_PSEUDO: return "Editer le <strong>pseudo</strong> des autres étudiants";
            case EDIT_OTHER_NOM: return "Editer le <strong>nom</strong> des autres étudiants";
            case EDIT_OTHER_PRENOM: return "Editer le <strong>prénom</strong> des autres étudiants";
            case EDIT_OTHER_SITUATION: return "Editer la <strong>situation</strong> des autres étudiants";
            case EDIT_OTHER_SITE: return "Editer le <strong>site</strong> des autres étudiants";
            case EDIT_OWN_EMAIL: return "Editer son propre <strong>email</strong>";
            case EDIT_OTHER_EMAIL: return "Editer l'<strong>email</strong> des autres étudiants";
            case EDIT_OWN_MDP: return "Editer son propre <strong>mot de passe</strong>";
            case EDIT_OTHER_MDP: return "Editer le <strong>mot de passe</strong> des autres étudiants";
            case EDIT_OWN_PORTABLE: return "Editer son propre <strong>portable</strong>";
            case EDIT_OTHER_PORTABLE: return "Editer le <strong>portable</strong> des autres étudiants";
            case EDIT_OTHER_MATIERES: return "Editer les <strong>matières</strong> des autres étudiants";
            case EDIT_OTHER_STATUT: return "Editer le statut des étudiants de <strong>statut</strong> inférieur";

            case SEE_MEMBRES: return "Voir la liste des <strong>étudiants</strong>";
            case SEE_PROFILS: return "Voir les <strong>profils</strong> des autres étudiants";
            case EDIT_PERMISSIONS: return "Accéder à la page des <strong>permissions</strong> et les modifier";
            case UPLOAD_RESULTS: return "Pouvoir uploader des <strong>résultats</strong>";
            case SEE_RESULTS: return "Voir les <strong>résultats</strong> des autres étudiants";
            case ADD_SUJET: return "Ajouter un nouveau <strong>sujet</strong> (dans sa propre matière)";
            case ADD_QUESTION: return "Ajouter une nouvelle <strong>question</strong> (dans sa propre matière)";
            case EDIT_SUJET: return "Editer le <strong>sujet</strong> d'un autre tuteur (dans sa propre matière)";
            case EDIT_QUESTION: return "Editer la <strong>question</strong> d'un autre tuteur (dans sa propre matière)";
            case SEE_QUESTIONS: return "Voir les <strong>questions</strong> des autres tuteurs (dans sa propre matière)";
            case SEE_SUJETS: return "Voir les <strong>sujets</strong> des autres tuteurs (dans sa propre matière)";
            case BANK: return "Banquer/débanque les <strong>question</strong> de sa matière";
            case ATTACH: return "Attacher/détacher une <strong>question</strong> à un/d'un sujet";
            case DELETE_QUESTION: return "Supprimer la <strong>question</strong> d'une autre tuteur";
            case DELETE_SUJET: return "Supprimer le <strong>sujet</strong> d'un autre tuteur";
            case DELETE_MEMBRE: return "Desinscrire un <strong>profil</strong> d'étudiant (il ne pourra plus accéder à son compte)";
            case SEE_QCMS: return 'Accéder à la <strong>banque de QCM</strong>';
            case SEE_SUJETS_ET_COR: return 'Voir les sujets et leurs corrections au format <strong>pdf</strong>';
            case ADD_MEMBRE: return 'Enregistrer des <strong>comptes</strong> étudiants';
        }
    }
}