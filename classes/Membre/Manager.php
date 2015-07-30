<?php

namespace Membre;

/**
 * 
 */

class Manager extends \Modele\Manager {
    
    protected function init() {
        $this->nature = 'Membre';
        $this->table = 'membres';
    }
    
    /**
     * Met à jour un membre de la BDD. Renvoie une erreur s'il a une idée non existante.
     * @access public
     * @param Data $membre le membre à mettre à jour
     * @return void
     */
    public  function update($membre) {
        if($this->idExiste($membre->getId())) {
            $membre_db = new \DB\SQL\Mapper($this->db,'membres');
            $membre_db->load(array('id=?', $membre->getId()));
            $ancien_statut = $membre_db->statut;
            
            // si il y a validation, on envoie un mail.
            if($ancien_statut == -1 && $membre->estValide()) {
                $membre->sendEmailAndGenerateMdp();
            }
            
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
    public  function add($membre) {
        if($this->pseudoExiste($membre->getPseudo())) throw new \Exception('L\'identifiant ' . $membre->getPseudo() . ' est déjà utilisé. Veuillez en choisir un autre.');
        if($this->idExiste($membre->getId())) throw new \Exception('Impossible d\'ajouter l\'étudiant : il existe déjà. Id : ' . $membre->getId());
            
        $membre_db = new \DB\SQL\Mapper($this->db,'membres');
        $membre->remplirMapper($membre_db);
        $membre->setId(NULL);
        $membre_db->insert();
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
     * Met à jour le profil contenu dans la variable session.user, si un membre est connecté.
     * Cela permet de prendre en compte des changement fait sur le profil d'un utilisateur pendant sa session de connexion.
     * @access public
     * @return void
     */
    public function refreshConnection() {
        $membre = $this->getConnected();
        if($membre) {
            $f3 = \Base::instance();
            $membre_maj = $this->getFromId($membre->getId());
            $f3->set('SESSION.user', $membre_maj);
        }
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
    
    public function parser($file) {
        require_once('reader.php');
        
        $reader = new \Spreadsheet_Excel_Reader();
        
        $reader->read($file);
        
        $data = $reader->sheets[0]['cells'];
        // premier indice -> ligne
        $champs_autorises = array('nom', 'prenom', 'site', 'situation', 'statut', 'email', 'matieres', 'portable');
        for ($j = 1; $j <= count($data[1]); $j++) {
            if(!in_array(strtolower($data[1][$j]), $champs_autorises)) throw new \Exception('La colonne n°' . $j . ' n\'a pas un nom valide. Pensez à enlever les accents');
            $champs[$j] = strtolower($data[1][$j]);
        }
        if(!in_array('nom', $champs)) throw new\Exception ('Colonne "nom" introuvable'); 
        if(!in_array('prenom', $champs)) throw new\Exception ('Colonne "prenom" introuvable'); 
        if(!in_array('site', $champs)) throw new\Exception ('Colonne "site" introuvable'); 
        if(!in_array('situation', $champs)) throw new\Exception ('Colonne "situation" introuvable'); 
        if(!in_array('email', $champs)) throw new\Exception ('Colonne "email" introuvable'); 
        
        for ($i = 2; $i <= count($data); $i++) {
            $membre_array = array();
            for ($j = 1; $j <= count($data[1]); $j++) {
                if(isset($data[$i][$j])) {
                    $membre_array[$champs[$j]] = $data[$i][$j];
                }
                else {
                    $membre_array[$champs[$j]] = '';
                }
            }
            
            try {
                $membre = new Data($membre_array);
                $membre->setPseudoFromNom();
                if(Manager::instance()->pseudoExiste($membre->getPseudo())) throw new \Exception ('L\'étudiant '.$membre->getNom().' '.$membre->getPrenom().' a déjà été ajouté.');
                $membre->sendEmailAndGenerateMdp();
                Manager::instance()->add($membre);
                \Msg::instance()->add(\Msg::STATUT_SUCCESS, 'L\'étudiant '.$membre->getNom().' '.$membre->getPrenom().' a bien été enregistré.');
            } catch (\Exception $ex) {
                \Msg::instance()->add(3, 'Erreur ligne '. $i . ' colonne ' . $j . ' : ' . $ex->getMessage());
            }
        }
    }
}