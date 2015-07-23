<?php

class Msg extends Prefab {
    const INSCRIPTION_SUCCESS = 'L\'inscription s\'est correctement déroulée. Vous recevrez votre mot de passe par mail quand un modérateur aura validé votre compte.';
    const STATUT_SUCCESS = 1;
    const STATUT_WARNING = 2;
    const STATUT_ERROR = 3;
    
    private $messages;
    
    public function add($statut, $chaine) {
        $this->messages[] = array($statut, $chaine);
    }
    
    public function getAll() {
        return $this->messages;
    }
    
    public function displayAll() {
        if(isset($this->messages)) {
            $texte = '';
            foreach ($this->messages as $msg) {
                $texte .= '<p style="color:';
                $texte .= $msg[0] == 1?'green':'red';
                $texte .= '">' . $msg[1] . '</p>';
            }
            echo $texte;
            unset($this->messages);
        }
    }
}