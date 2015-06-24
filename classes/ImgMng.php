<?php

class ImgMng {
    protected $folder;
    protected static $instance;
    protected $img_extensions = array('jpg', 'jpeg', 'png');
    const MAX_SIZE = 2000000;

    protected function construct__() {
        $this->folder = Base::instance()->get('UPLOAD');
    }
    
    public static function getInstance() {
        if(!isset(self::$instance)) {
            self::$instance = new ImgMng();
        }
        return self::$instance;
    }
    
    /* APPELE VIA AJAX
     * réception d'une image via ajaxForm et stockage dans le dossier
     * @access public
     * renvoie en echo (cf ajax) le chemin absolue jusqu'à l'image
     */
    public function imageUpload() {
        // Array qui sera serialized puis envoyé comme echo
        
        $web = \Web::instance();
        $files = $web->receive(
            function($file, $formFieldName) {
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
    
    /* APPELE VIA AJAX
     * Suppression d'une image via ajax
     * @access public
     * @return void
     */
    public function imageDelete($f3) {
        $filename = $f3->get('POST.image_path');
        if($this->estUneImage($filename)) {
            unlink('files/images/edit2.png');
        }
    }
    
    /*
     * Renvoie TRUE si il s'agit d'une image, FALSE sinon
     * @access public
     * @param nom_fichier le nom du fichier à évaluer
     * @return bool
     */
    
    public function estUneImage($nom_fichier) {
        $extension = pathinfo($nom_fichier, PATHINFO_EXTENSION);
        return in_array($extension, $this->img_extensions);
    }
}