<?php

class Membre {
    
    public $id;
    public $pseudo;
    
    /* /!\ Attention, seul $this->id initialisé, si on veut utiliser this->pseudo il faut d'abord le stocker */
    
    public function __construct($id) {
        $this->id = $id;
    }
    
    /*
     * Partie VUE
     */
       
    public function afficherProfil($f3) {
        $membre = new Membre($f3->get('PARAMS.id'));
        $f3->set('membre', $membre->getMembreFromId($f3));
        $f3->set('membre_class', $membre);
        
        $f3->set('afficherBoutonModifier', function ($f3, $champ) {
            if($this->estAutoriseAModifier($f3, $f3->get('PARAMS.id'), $champ)) {
                echo '<img class="bouton" onclick="modifier(\'' . $champ . '\', \''
                .$f3->get('membre')[$champ]
                .'\')" src="' . $f3->get('root') . 'files/images/edit.png" alt="Modifier" />';
            }
        });
        
        afficherPage($f3, 'templates/profil.htm');
    }
    
    // Liste les matières attitrées dans une liste (entourés de balises <li>) avec un lien de suppression
    public function listerMatieres($f3) {
        $matieres = $this->getMembreFromId($f3)->matieres;
        $retour = '<ul>';
        $inc = 1;
        while($matieres != 0) {
            if(getBit($matieres, $inc)) {
                $retour .= '<li>'. $f3->get('matieres')[$inc]
                        .' <img class="bouton" onclick="supprimerMatiere(\'' . $inc
                        .'\')" src="' . $f3->get('root') . 'files/images/delete.png" alt="Supprimer" /></li>';
                $matieres = $matieres-pow(10, $inc-1);
            }
            $inc++;
        }
        return $retour . '</ul>';
    }
    
    //Liste les matières non attitrées entourées de balise <option> pour un bouton select(menu déroulant)
    public function listerMatieresNonSuivies($f3) {
        $matieres = $this->getMembreFromId($f3)->matieres;
        foreach($f3->get('matieres') as $key=>$value) {
            if(!getBit($matieres, $key)) {
                echo '<option value="' . $key . '">' . $value . '</option>';
            }
        }
    }
    
    //Liste les matières attitrées entourées de balise <option> pour un bouton select
    public function matieresSuiviesEnOption($f3) {
        $matieres = $this->getMembreFromId($f3)->matieres;
        foreach($f3->get('matieres') as $key=>$value) {
            if(getBit($matieres, $key)) {
                echo '<option value="' . $key . '">' . $value . '</option>';
            }
        }
    }
    
    /*
     * Partie CONTROLLEUR
     */
    
    public function connexion($f3) {
        $this->pseudo = $f3->get('POST.pseudo');
        // On vérifie que l'utilisateur existe
        if($this->pseudoExiste($f3)) {
            // Concordance des mdp
            if(sha1($f3->get('POST.mdp')) == $this->getMembreFromPseudo($f3)->mdp) {
                $this->id = $this->getMembreFromPseudo($f3)->id;
                $f3->set('SESSION.user', $this);
            }
            else
                $f3->set('erreur', 'Erreur de connexion : mauvais mot de passe');
        }
        else {
            $f3->set('erreur', 'Erreur de connexion : mauvais identifiant');
        }
        
        $f3->reroute('/');
    }
    
    public function deconnexion($f3) {
        $f3->clear('SESSION.user');
        $f3->reroute('/');
    }
    
    public function modifierChamp($f3) {
        if($this->estAutoriseAModifier($f3, $f3->get('POST.id'), $f3->get('POST.champ')))
            $this->majChamp($f3);
        else
                echo 'Vous n\'avez les autorisations nécessaires pour modifier ce champ';
    }
    
    public function estAutoriseAModifier($f3, $id_a_modifier, $champ) {
        /* On regarde si l'utilisateur tente de modifier son propre champ */
        if($id_a_modifier == $f3->get('SESSION.user')->id AND verifierPermission($f3, 'modifier_propre_'.$champ)
                /* ou si il a les droit de modifier celui des autre */
                OR verifierPermission($f3, 'modifier_autre_'.$champ)) {
            return true;
        }
        else
            return false;
    }
    
    public function supprimerMembre($f3) {
        if(verifierPermission($f3, 'supprimer_membre')) {
            $this->id = $f3->get('POST.id');
            $this->supprimer($f3);
        }
        else
            echo 'Vous n\'avez les autorisations nécessaires pour effectuer cette action.';
    }
    
    
    
    /*
     * Partie MODELE
     */
    
    public function sauvegarderMembre($f3) {
        $db = $f3->get('Bdd');
        $membre = new DB\SQL\Mapper($db,'membres');
        
        // On regarde s'il s'agit d'une update (id /= 0)
        if($this->id) $membre->load(array('id=?', $this->id));
        
        $membre->copyfrom('POST');
        $membre->mdp = sha1($membre->mdp);
        $membre->matieres = 0;
        foreach ($f3->get('matieres') as $key=>$value) {
            $membre->matieres += ($f3->get('POST.matiere' . $key) == 'on')*pow(10, $key-1);
        }
        
        $membre->save();
    }
    
    public function pseudoExiste($f3) {
        $db = $f3->get('Bdd');
        $membre = new DB\SQL\Mapper($db,'membres');
        $membre->load(array('pseudo=?', $this->pseudo));
        return (sizeof($membre) == 1);
    }
    
    public function getMembreFromPseudo($f3) {
        $db = $f3->get('Bdd');
        $membre = new DB\SQL\Mapper($db,'membres');
        return $membre->load(array('pseudo=?', $this->pseudo));
    }
    
    public function getMembreFromId($f3) {
        $db = $f3->get('Bdd');
        $membre = new DB\SQL\Mapper($db,'membres');
        return $membre->load(array('id=?', $this->id));
    }
    
    public function getPseudo($f3, $id) {
        $db = $f3->get('Bdd');
        $membre = new DB\SQL\Mapper($db,'membres');
        return $membre->load(array('id=?', $id))['pseudo'];
    }
    
    public function majChamp($f3) {
        $db = $f3->get('Bdd');
        $champ = $f3->get('POST.champ');
        $valeur = $f3->get('POST.valeur');
        $this->id = $f3->get('POST.id');

        $db->exec('UPDATE membres SET ' . $champ . '=:valeur WHERE id=:id', array('valeur' => $valeur, 'id' => $this->id));
        
    }
    
    public function supprimer($f3) {
        $db = $f3->get('Bdd');
        $db->exec('DELETE FROM membres WHERE id=?', $this->id);
    }
}