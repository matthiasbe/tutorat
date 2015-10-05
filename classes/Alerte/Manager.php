<?php

namespace Alerte;

/**
 * Gère les notifications. Nouvelles questions, nouveaux sujets.
 * Classe manager : interactions avec la base de données.
 * \Modele\Manager : intégre les fonctions de gestion d'une table.
 */

class Manager extends \Modele\Manager {
    protected function init() {
        $this->nature = 'Alerte';
        $this->table = 'alertes';
    }
}