<?php

namespace Membre;

/**
 * Classe appelée pour afficher les pages rélatives aux membres
 */

class View {
    
    /**
     * Affiche l'espace membre avec un onglet profil, et un onglet résultats.
     * @access public
     * @param \Base $f3
     * @return void
     */
    public function profil($f3) {
        $membre = Manager::instance()->getFromId($f3->get('PARAMS.id'));
        $est_ce_profil_du_connecte = $f3->get('PARAMS.id') == Manager::instance()->getConnected()->getId();
        if(CIA(SEE_PROFILS) || $est_ce_profil_du_connecte) {
            $f3->set('membre', $membre);

            // onglet PROFIL
            /* Fonction permettant d'afficher le bouton de modification des champs :
             * ce bouton appelle une fonction javascript 'modifier' qui afficher
             * un popup contenant l'ancienne valeur ainsi qu'un bouton ok mettant
             * à jour le champ en ajax */
            $f3->set('afficherBoutonModifier', function ($champ) {
                $f3 = \Base::instance();
                if(\Membre\Manager::instance()->estAutoriseAModifier($f3->get('membre')->getId(), $champ)) {
                    $getter = 'get' . ucfirst($champ);
                    echo '<img class="bouton" onclick="modifier(\'' . $champ . '\', \''
                    .$f3->get('membre')->$getter()
                    .'\')" src="' . $f3->get('root') . 'files/images/edit.png" alt="Modifier" />';
                }
            });

            // Onglet RESULTATS
            $resultats = $membre->getAllResultats();
            $f3->set('resultats', $resultats);

            afficherPage('templates/membre/espace.htm');
        }
        else {
            afficherPage(PAGE_ERREUR);
        }
    }
    /**
     * POST.old_mdp : ancien mdp
     * POST.new_mdp1 : nouveau mdp
     * POST.new_mdp2 : confirmation du nouveau mdp
     * @access public
     * @param Base $f3
     * @return void
     */
    public function receptionModifierMdp($f3) {
        $id_membre = $f3->get('PARAMS.id');
        if(Manager::instance()->estAutoriseAModifier($id_membre, 'mdp')) {
            $old = $f3->get('POST.old_mdp');
            $new1 = $f3->get('POST.new_mdp1');
            $new2 = $f3->get('POST.new_mdp2');
            $membre = Manager::instance()->getFromId($id_membre);
            
            if($new1 == $new2) {
                if($membre->testMdp($old)) {
                    $membre->setMdp(sha1($new1));
                    Manager::instance()->update($membre);
                    echo 'Mot de passe enregistré.';
                }
                else
                    echo 'L\'ancien mot de passe entré est incorrect.';
            }
            else
                echo 'Les deux nouveau mots de passe sont différents.';
        }
        $this->profil($f3);
    }
    
    /**
     * Effectue la connexion d'un membre à partir du formulaire envoyé en POST.
     * Puis redirige vers l'ancienne page
     * POST.pseudo contient le pseudo du membre
     * POST.mdp contient le mot de passe entré
     * GET.v contient la page qui était visitée
     * @access public
     * @param Base $f3
     * @return void
     */
    public function connexion($f3) {
        $pseudo = $f3->get('POST.pseudo');
        $mdp = $f3->get('POST.mdp');
        
        try {
            Manager::instance()->connect($pseudo, $mdp);
            $f3->reroute(preg_replace('#^/fatfree#', '', $f3->get('GET.v')));
        } catch (\Exception $ex) {
            echo 'Une erreur est survenue lors de la connexion : ' . $ex->getMessage();
        }
    }
    
    /**
     * Déconnecte le membre actuel
     * @access public
     * @param Base $f3
     * @return void
     */
    public function deconnexion($f3) {
        Manager::instance()->disconnect();
        $f3->reroute('/');
    }
    
    /**
     * Réception du formulaire d'ajout de membres.
     * Enregistre le membre dans la base de données.
     * POST contient les champs à ajouter.
     * @access
     * @param Base $f3
     * @return void
     */
    public function save($f3) {
        if(CIA(ADD_MEMBRE)) {
            $membre = new Data($f3->get('POST'));
            $membre->setMdp(sha1($f3->get('POST.mdp')));
            unset($membre->matiere);
            $membre->setMatieres(0);
            foreach(array_keys($f3->get('matieres')) as $cle) {
                if($f3->get('POST.matiere' . $cle)) {
                    $membre->setMatiere($cle, 1);
                }
            }
            Manager::instance()->add($membre);
            $f3->reroute('/admin/membres/ajouter');
        }
        else {
            echo ERREUR;
        }
    }
    
    /**
     * Affiche la page contenant la liste des membres.
     * @access public
     * @param Base $f3
     * return void
     */
    public function liste ($f3) {
        if(CIA(SEE_MEMBRES)) {
            $f3->set('membres', Manager::instance()->getAll());
            afficherPage('templates/admin/membres/liste.htm');
        }
        else
            afficherPage (PAGE_ERREUR);
    }
    
    /**
     * Affiche la page d'ajout de membre.
     * @access public
     * @param Base $f3
     * return void
     */
    public function ajouter ($f3) {
        if(CIA(ADD_MEMBRE)) {
            afficherPage('templates/admin/membres/ajouter.htm');
        }
        else
            afficherPage (PAGE_ERREUR);
    }
    
    /**
     * Affiche page de suppression de membre.
     * @access public
     * @param Base $f3
     * return void
     */
    public function supprimer ($f3) {
        if(CIA(DELETE_MEMBRE)) {
            afficherPage('templates/admin/membres/supprimer.htm');
        }
    }
    
    /**
     * Réception du formulaire d'ajout de membre.
     * @access public
     * @param Base $f3
     * return void
     */
    public function receptionAjouter($f3) {
        if(CIA(ADD_MEMBRE)) {
            $membre = new Membre(0);
            $membre->sauvegarderMembre($f3);
            $f3->reroute('/admin/membres/ajouter');
        }
        else {
            echo ERREUR;
        }
    }
    
    /**
     * Affiche la page d'inscription.
     * Si requète POST, on ajoute le membre sans l'activer (statut = -1).
     * POST.nom : le nom de l'étudiant.
     * POST.prenom : son prénom.
     * POST.situation
     * POST.email
     * POST.portable
     * POST.site
     * @access public
     * @param Base $f3
     * @return void
     */
    public function inscription($f3) {
        if(count($f3->get('POST')) == 0) {
            afficherPage('templates/membre/inscription.htm');
        }
        else {
            $membre = new Data(array(
                'nom' => $f3->get('POST.nom'),
                'prenom' => $f3->get('POST.prenom'),
                'situation' => $f3->get('POST.situation'),
                'site' => $f3->get('POST.site'),
                'email' => $f3->get('POST.email'),
                'portable' => $f3->get('POST.portable'),
                'site' => $f3->get('POST.site'),
            ));
            $membre->setStatut(-1);
            $membre->setPseudoFromNom();
            Manager::instance()->add($membre);
            
            \Msg::instance()->add(\Msg::STATUT_SUCCESS, \Msg::INSCRIPTION_SUCCESS);
            $gl = new \General;
            $gl->AfficherAccueil($f3);
        }
    }
    
}