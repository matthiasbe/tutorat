<?php

namespace Statut;

/* 
 * Reçoit les requètes ajax
 */

class Ajax {
    /**
     * Reçoit la requète Ajax de mise à jour lorsqu'une checkbox est cochée/décochée dans le panneau des permission.
     * POST.statut : le statut à mettre à jour.
     * POST.permission : la permission à mettre à jour.
     * POST.value : la nouvelle valeur de cette permission.
     * @access public
     * @param Base $f3
     * @return void
     */
    public function edit($f3) {
        if(CIA(EDIT_PERMISSIONS)) {
            $statut = Manager::instance()->getFromId($f3->get('POST.statut'));
            $num_perm = $f3->get('POST.permission');
            $value = ($f3->get('POST.value') == 'true');

            $statut->setPermission($num_perm, $value);
            Manager::instance()->update($statut);
        }
        else {
            echo ERREUR;
        }
    }
    
    /**
     * Ajouter un rôle à la base de donnée.
     * POST.nom : le nom du rôle à ajouter.
     * @access public
     * @param Base $f3
     * @return void
     */
    public function add($f3) {
        $nom = $f3->get('POST.nom');
        if(CIA(EDIT_PERMISSIONS)) {
            $statut = new Data(array(
                'nom' => $nom,
                'permissions' => 0 // Un nouveau rôle n'à aucun droit à la création
            ));
            Manager::instance()->add($statut);
        }
        else {
            echo ERREUR;
        }
    }
}