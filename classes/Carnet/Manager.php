<?php

namespace Carnet;

/**
 * Ajout et suppression des carnets de cours. Se sont pour l'instant des PDF.
 * Classe manager : interactions avec la base de données.
 * Prefab : Permet de créer un classe singleton (une seul instance que l'on appelle avec la méthode static ::instance())
 */

class Manager extends \Prefab {
    
    
    /**
     * @var String Le chemin vers le repertoire des carnets.
     */
    private $folder;
    /**
     * @var String Chemin vers le repertoire des carnet avec le prefix root (variable globale contenant l'adresse du sous-domaine)
     */
    private $folder_with_root;
    
    /**
     * Initialise les propriété de la classe.
     */
    public function __construct() {
        $f3 = \Base::instance();
        $this->folder_with_root = $f3->get('root') . 'files/carnets';
        $this->folder = 'files/carnets';
        $this->initDir();
    }
    
    /**
     * Permet de récupérer les noms de tous les carnets présent dans le dossier.
     * @return array Un tableau contenant tous les nom de fichiers des carnets.
     */
    public function getAll() {
        $f3 = \Base::instance();
        $matieres = $f3->get('matieres');
        $carnets = array();
        foreach ($matieres as $key=>$value) {
            $carnets[$key] = $this->getFromMatiere($key);
        }
        return $carnets;
    }
    
    /**
     * Récupère tous les nom des carnets présents dans le dossier pour une matière donnée.
     * @param int $num_matiere Le numéro de la matière.
     * @return array Un tableau contenant tous nom de fichier des carnets pour la matière demandée.
     */
    public function getFromMatiere($num_matiere) {
        $fichiers = scandir($this->folder . '/' . $num_matiere);
        $carnets = array();
        foreach($fichiers as $fichier) {
            if(preg_match('#^.*\.pdf$#', $fichier)) {
                $carnets[] = $fichier;
            }
        }
        
        return $carnets;
    }
    
    /**
     * Permet de créer les dossiers nécessaires pour enregistrer les carnets.
     * Un dossier pour le tout et un sous-dossier par matière.
     */
    public function initDir() {
        if(!file_exists($this->folder)) {
            mkdir($this->folder);
        }
        $f3 = \Base::instance();
        foreach($f3->get('matieres') as $key=>$value) {
            if(!file_exists($this->folder . '/' . $key)) {
                mkdir($this->folder . '/' . $key);
            }
        }
    }
    
    public function getFolderWithRoot() {
        return $this->folder_with_root;
    }
    
    /**
     * Permet de gérer les demandes de la requête : si un fichier à été envoyé, on ajoute
     * le carnet. Si il existe GET.del, on supprime le carnet correspondant.
     * @acces public
     * @return void
     */
    public function addAndDeleteFromRequest() {
        $f3 = \Base::instance();
        $web = \Web::instance();
        
        // Si on a l'autorisation et les données pour ajouter un carnet, on le fait
        if(CIA(ADD_CARNET) && $web->receive()) {
            // Ajout du carnet uploadé
            $this->addFromRequest();
        }
        // Si on a l'autorisation et les données GET pour supprimer un carnet, on le fait
        if(CIA(DELETE_CARNET) && $f3->exists('GET.del')) {
            // Suppression du carnet
        }
    }
    
    /**
     * Appelé par un formulaire d'ajout de fichier pour ajouter un carnet.
     * @throws Exception
     */
    public function addFromRequest() {
        $web = \Web::instance();
        // Si on ne trouve pas le fichier envoyé, c'est que cette fonction a été appelée par erreur.
        if(!$web->receive()) new \Exception ('Aucun fichier envoyé.');
        $success = $web->receive(
            function ($file, $formFieldName) {
                
            },
            false, // false -> les fichiers du même nom ne sont pas remplacés
            true // true -> le nom de fichier formatté
        );
    }
}