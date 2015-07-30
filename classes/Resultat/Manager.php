<?php

namespace Resultat;

/**
 * 
 */

class Manager extends \Modele\Manager {
    
    protected function init() {
        $this->nature = 'Resultat';
        $this->table = 'resultats';
    }
    
    /**
     * Récupère un résultat dans la bdd à partir de son id de membre.
     * @param int $id_membre
     * @return array un tableau contenant les résultats du membre
     */
    
    public function getFromMembre($id_membre) {
        if(\Membre\Manager::instance()->idExiste($id_membre)) {
            $res_array = $this->db->exec('SELECT * FROM resultats WHERE membre=?', $id_membre);
            return $this->results2objects($res_array);
        }
        else {
            trigger_error('Membre inconnu. (fonction getFromMembre)');
        }
    }
    
    /**
     * A partir des lignes d'une fichier ouvert avec la fonction file(), récupère les résultats
     * des différents membres pour un sujet donné et les enregistrer dans la base de donnée.
     * Le FORMAT des fichier à fournir est le suivant :
     * *****
     * Id du sujet
     * Id du premier participant (numero d'anonymat)
     * réponse à la Q1
     * réponse à la Q2
     * ...
     * Id du deuxieme participant
     * réponse à la Q1
     * ...
     * *****
     * @param array $lignes
     * @throws \Exception
     * @return void
     */
    public function parserEtEnregistrer($lignes) {
        $nbr_lignes = count($lignes);
                
        if($nbr_lignes < 3) throw new \Exception('Le fichier doit contenir au moins 3 lignes');

        // Ligne 0 : le sujet
        $sujet = \Sujet\Manager::instance()->getFromId($lignes[0]);
        if(!$sujet) throw new \Exception('Sujet inconnu.'); // getFromId renvoie NULL si l'id n'existe pas

        // On récupère le nombre de questions du sujet depuis la bdd
        $nbr_questions = $sujet->getNombre_questions();
        if($nbr_questions == 0) throw new \Exception('Le sujet ne contient pas de questions');

        // on vérifie qu'on a bien $nbr_ligne = $nbr_question * $nbr_membres + 1
        $nbr_membres = ($nbr_lignes - 1)/($nbr_questions + 1);
        if($nbr_membres != round($nbr_membres)) throw new \Exception ('Le nombre de lignes du fichier ne correspond pas. On doit avoir nbr_lignes = 1 + nbr_question_du_sujet * nbr_participants.');
        for($i = 0; $i < $nbr_membres; $i++) {
            // Pour chaque membre, on enregistre ses résultats
            $ligne_membre = $i*($nbr_questions + 1) + 1;
            $membre = \Membre\Manager::instance()->getFromId($lignes[$ligne_membre]);
            if($membre == NULL) throw new \Exception('Membre inconnu. Ligne : ' . ($ligne_membre + 1));
            for($j = 1; $j <= $nbr_questions; $j++) {
                $reponse = $lignes[$ligne_membre + $j];
                if($reponse >= pow(2,5)) throw new \Exception ('Les réponses aux questions doivent être inférieures ou égales à ' . (pow(2,5)-1) . '. Ligne : ' . ($ligne_membre + $j + 1) . ' Ici le résultat vaut : ' . $reponse);
                $resultats[$j] = $reponse;
            }
            $resultat = new Data(array('membre' => $membre, 'sujet' => $sujet, 'resultats' => $resultats));
            $this->add($resultat);
        }
    }

}