<?php

namespace Resultat;

/**
 * 
 */

class Data extends \Modele\Data {
    
    /**
     * @var int Id du membre auquel appartient le résultat.
     * @access private
     */
    protected  $membre;

    /**
     * @var int id du sujet pour lequel on donne le résultat.
     * @access private
     */
    protected  $sujet;

    /**
     * @var array tableau d'entier avec le numero de la question en indice et le résultat aux item sur 5 bits correspondant.
     * @access private
     */
    protected  $resultats;


    /**
     * @access public
     * @return \Membre\Data
     */

    public  function getMembre() {
        return \Membre\Manager::instance()->getFromId($this->membre);
    }


    /**
     * 	
     * @access public
     * @param mixed $membre 
     */

    public  function setMembre($membre) {
        if(is_a($membre, '\Membre\Data')) {
            $this->membre = $membre->getId();
        }
        elseif(is_string($membre)) {
            $this->membre = $membre;
        }
    }


    /**
     * @access public
     * @return \Sujet\Data
     */

    public  function getSujet() {
        return \Sujet\Manager::instance()->getFromId($this->sujet);
    }


    /**
     * @access public
     * @param mixed $sujet 
     */

    public  function setSujet($sujet) {
        if(is_a($sujet, '\Sujet\Data')) {
            $this->sujet = $sujet->getId();
        }
        elseif(is_string($sujet)) {
            $this->sujet = $sujet;
        }
    }


    /**
     * @access public
     * @param array $resultats
     * @return void
     */

    public  function setResultats($resultats) {
        if(is_string($resultats)) {
            $this->resultats = $resultats;
        }
        else {
            $this->resultats = serialize($resultats);
        }
    }


    /**
     * @access public
     * @return array
     */

    public  function getResultats() {
        return unserialize($this->resultats);
    }


    /**
     * Calcul la note du candidat en comparant les réponses aux correction
     * @access public
     * @return string la note du candidat
     */

    public  function getNote() {
        $note = 0;
        $total = 0;
        foreach($this->getResultats() as $cle => $reponse) {
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
        return $this->resultats;
    }
}