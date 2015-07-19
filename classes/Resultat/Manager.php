<?php

namespace Resultat;

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
     * @access public
     * @param Data $resultat le membre à mettre à jour. Ce résultat doit déjà exister dans la BDD.
     * @return void
     */

    public  function update($resultat) {
        trigger_error('la fonction update results n\'est pas encore défnie');
        if(!idExiste($resultat->getId())) trigger_error ('Fonction UPDATE sur un résultat n\'existant pas dans la BDD.');

    }


    /**
     * Enregistre un nouveau résultat dans la base de données.
     * @access public
     * @param Data $resultat Le résultat à ajouter.
     * @return
     */

    public  function add($resultat) {
        if($this->idExiste($resultat->getId())) trigger_error ('Fonction ADD sur un résultat existant déjà dans la BDD.');
        
        $res_db = new \DB\SQL\Mapper($this->db, 'resultats');
        $resultat->remplirMapper($res_db);
        $res_db->save();
    }
    
    /**
     * Récupère un résultat dans la bdd à partir de son ID.
     * @param int $id_res
     * @return \Membre\Data Le résultat ayant pour id $id_res, NULL sinon.
     */
    
    public function getFromId($id_res) {
        if($this->idExiste($id_res)) {
            $res_array = $this->db->exec('SELECT * FROM resultats WHERE id=?', $id_res)[0];
            return new Data($res_array);
        }
        else {
            return NULL;
        }
    }
    
    /**
     * Récupère un résultat dans la bdd à partir de son id de membre.
     * @param int $id_membre
     * @return array un tableau contenant les résultats du membre
     */
    
    public function getFromMembre($id_membre) {
        if(\Membre\Manager::instance()->idExiste($id_membre)) {
            $resultats = array();
            $res_array = $this->db->exec('SELECT * FROM resultats WHERE membre=?', $id_membre);
            foreach($res_array as $key=>$res) {
                $resultats[$key] = new Data($res);
            }
            return $resultats;
        }
        else {
            trigger_error('Membre inconnu. (fonction getFromMembre');
        }
    }
    
    /**
     * Détermine si un résultat existe ou non dans la BDD.
     * @access public
     * @param int $id
     * @return bool True si un résultat avec l'id donné existe et est unique
     */
    public function idExiste($id) {
        $resultat = new \DB\SQL\Mapper($this->db,'resultats');
        $nombre_resultats = $resultat->count(array('id=?', $id));
        return ($nombre_resultats == 1);
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