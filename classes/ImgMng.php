<?php
/**
 * Gère les différentes images ajoutés aux sujets.
 * Cette classe n'utilise pas le format MVC comme les autres comme les autres car crée avant, il faudrait l'adapter.
 */
class ImgMng {
    
    /**
     * @var String Le chemin vers le repertoire des images.
     */
    private $folder;
    /**
     * @var String Chemin vers le repertoire des images avec le prefix root (variable globale contenant l'adresse du sous-domaine)
     */
    private $folder_with_root;
    
    private static $instance;
    
    /**
     * @var array Les différentes extensions d'images autorisées.
     */
    private $img_extensions = array('jpg', 'jpeg');
    const MAX_SIZE = 2000000;

    public function __construct() {
        $f3 = Base::instance();
        $this->folder_with_root = $f3->get('root') . $this->folder;
        $this->folder= 'files/dl/';
        if(!isset(self::$instance)) {
            self::$instance = $this;
        }
    }
    
    public static function getInstance() {
        if(!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getFolder() {
        return $this->folder;
    }
    
    /**
     * APPELE VIA AJAX Réception d'une image via ajaxForm et stockage dans le dossier.
     * Renvoie en echo (cf ajax) le chemin absolue jusqu'à l'image
     * @access public
     * @return void
     */
    public function imageUpload() {
        //On paramètre le dossier de sauvegarde
        $this->setF3UploadFolder();
        
        $web = \Web::instance();
        // On crée la fonction de callback
        $files = $web->receive(
            function($file) {
                global $retour;
                // On lève les différentes erreurs possible
                if(!$this->estUneImage($file['name'])) {
                    $retour['erreur'] = true;
                    $retour['message'] = 'Le fichier uploadé n\'est pas une image.';
                    echo json_encode($retour);
                    return false;
                }
                
                if($file['size'] > self::MAX_SIZE) {
                    $retour['erreur'] = true;
                    $retour['message'] = 'Le fichier uploadé a une taille supérieur à 2 Mo.';
                    echo json_encode($retour); // Array qui est serialized puis envoyé comme echo
                    return false;
                }
                
                // Si aucune erreur, on procède à l'upload
                $retour['erreur'] = false;
                $retour['image_path'] = Base::instance()->get('root') . $file['name'];
                echo json_encode($retour);
                return true; // Permet le transfère de tmp aux dossier UPLOAD
            },
            false, // false -> les fichiers du même nom ne sont pas remplacés
            true // true -> le nom de fichier formatté
        );
        foreach ($files as $success) {
            if(!$success) {
                $retour['erreur'] = true;
                $retour['message'] = 'Image non uploadée. Renommez la et réessayez.';
                echo json_encode($retour); // Array qui est serialized puis envoyé comme echo
            }
        }
    }
    
    /** APPELE VIA AJAX
     * Suppression d'une image via ajax
     * @access public
     * @return void
     */
    public function imageDelete($f3) {
        $path = $f3->get('POST.image_path');
        $filename = pathinfo($path, PATHINFO_BASENAME);
        if($this->estUneImage($filename)) {
            unlink($this->folder. $filename);
        }
    }
    
    /**
     * Renvoie TRUE si il s'agit d'une image, FALSE sinon
     * @access public
     * @param nom_fichier le nom du fichier à évaluer
     * @return bool
     */
    
    public function estUneImage($nom_fichier) {
        $extension = pathinfo($nom_fichier, PATHINFO_EXTENSION);
        return in_array($extension, $this->img_extensions);
    }
    
    /**
     * Règle la variable Uploads de FatFree pour que les images envoyées arrivent dans le bon fichier 
     * au moment de la commande web->receive()
     * A utiliser juste avant chaque appel de la classe à cette fonction;
     */
    public function setF3UploadFolder() {
        $f3 = \Base::instance();
        $f3->set('UPLOADS', $this->folder . '/');
    }
}