<?php

namespace Resultat;

/**
 * 
 */

class Data {

    /**
     * @var int
     * @access private
     */
    private  $id;

    /**
     * @var \Membre\Data Membre auquel appartient le résultat.
     * @access private
     */
    private  $membre;

    /**
     * @var \Sujet\Data Sujet pour lequel on donne le résultat.
     * @access private
     */
    private  $sujet;

    /**
     * @var array tableau d'entier avec le numero de la question en indice et le résultat aux item sur 5 bits correspondant.
     * @access private
     */
    private  $resultats;
    
    /**
     * Hydrate la classe avec un tableau de valeurs.
     * @access public
     * @param array $donnees Doit contenir au moins un champ id ou pseudo. Id est prioritaire.
     */
    public function __construct($donnees) {
        if(!is_array($donnees)) {
            trigger_error('Pour créer une classe Resultat\Data, un array doit être passé en paramètre.');
        }
        $this->hydrate($donnees);
    }

    /**
     * @access public
     * @param Array $donnees
     */
    public function hydrate($donnees) {
        foreach($donnees as $cle=>$valeur) {
            switch ($cle) {
                case 'membre':
                    $this->membre = \Membre\Manager::instance()->getFromId($valeur);
                    break;
                case 'sujet':
                    $this->sujet = \Sujet\Manager::instance()->getFromId($valeur);
                    break;
                case 'resultats':
                    $this->resultats = unserialize($valeur);
                    break;
                default:
                    $this->$cle = $valeur;
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
     * @return \Membre\Data
     */

    public  function getMembre() {
        return $this->membre;
    }


    /**
     * 	
     * @access public
     * @param \Membre\Data $membre 
     */

    public  function setPseudo(\Membre\Data $membre) {
        $this->membre = $membre;
    }


    /**
     * @access public
     * @return \Sujet\Data
     */

    public  function getSujet() {
        return $this->sujet;
    }


    /**
     * @access public
     * @param \Sujet\Data $sujet 
     */

    public  function setSujet(\Sujet\Data $sujet) {
        $this->sujet = $sujet;
    }


    /**
     * @access public
     * @param array $resultats
     * @return void
     */

    public  function setResultats($resultats) {
        $this->resultats = $resultats;
    }


    /**
     * @access public
     * @return array
     */

    public  function getResultats() {
        return $this->resultats;
    }


    /**
     * Calcul la note du candidat en comparant les réponses aux correction
     * @access public
     * @return string la note du candidat
     */

    public  function getNote() {
        $note = 0;
        $total = 0;
        foreach($this->resultats as $cle => $reponse) {
            $question = \Question\Manager::instance()->getFromNum($this->getSujet()->getId(), $cle);
            $correction = $question->getReponses();
            $note += $this->comparerReponses($reponse, $correction);
            $total += 5;
        }
        return $note . '/' . $total;
    }
    
    /**
     * Renvoie la note pour une question.
     * @param int $rep1
     * @param int $rep2
     * @return int
     */
    public function comparerReponses($rep1, $rep2) {
        $somme = 0;
        for($i = 1; $i <= 5; $i++) {
            if(getBit($rep1, $i) == getBit($rep2, $i)) {
                $somme++;
            }
        }
        return $somme;
    }
    
    /**
     * @access public
     * @return array
     */

    public  function getResultatsSerialized() {
        return serialize($this->resultats);
    }
    
    /**
     * Remplit un Mapper SQL avec les éléments de l'objet courant.
     * @access public
     * @param \DB\SQL\Mapper $mapper
     * @return void
     */
    public function remplirMapper(\DB\SQL\Mapper $mapper) {
        foreach ($this as $key=>$value) { // On parcourt tous les éléments de l'objet courant
            switch ($key) {
                case 'resultats':
                    $mapper->resultats = $this->getResultatsSerialized();
                    break;
                case 'membre' :
                    $mapper->membre = $this->getMembre()->getId();
                    break;
                case 'sujet' :
                    $mapper->sujet = $this->getSujet()->getId();
                    break;
                default :
                    $mapper->$key = $value;
            }
        }
    }
}