<?php

/**
 * Created by PhpStorm.
 * User: hyds
 * Date: 12/11/16
 * Time: 00:52
 */

namespace App\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;

class CommandeModel
{
    private $db;

    public function __construct(Application $app)
    {
        $this->db = $app['db'];
    }

    public function createCommande($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('commandes')
            ->values([
                'id' => '?',
                'user_id' => '?',
                'prix' => '?',
                'date_achat' => '?',
                'etat_id' => '?'
            ])
            ->setParameter(0, $donnees['id'])
            ->setParameter(1, $donnees['user_id'])
            ->setParameter(2, $donnees['prix'])
            ->setParameter(3, $donnees['date_achat'])
            ->setParameter(4, $donnees['etat_id'])
        ;
        return $queryBuilder->execute();
    }
    public function findCommadeWithoutPrice($id_user){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('*')
            ->from('commandes')
            ->where('user_id= ? and prix=0')
            ->setParameter(0, $id_user);

        return $queryBuilder->execute()->fetch();
    }

    public function setupPriceCommande($id_commande,$price){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('commandes')
            ->set('prix','?')
            ->where('id= ?')
            ->setParameter(0,$price)
            ->setParameter(1,$id_commande)
        ;
        return $queryBuilder->execute();

    }
}