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

class PanierModel
{
    private $db;

    public function __construct(Application $app) {
        $this->db = $app['db'];
    }

    public function getAllPanier($id_user){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            -> select('p.nom','p.photo','pa.quantite','pa.prix,pa.produit_id')
            ->from('paniers','pa')
            ->innerJoin('pa','users','u','pa.user_id=u.id')
            ->innerJoin('pa','produits','p','pa.produit_id=p.id')
            ->where('pa.user_id='.$id_user)
            ->addOrderBy('p.nom','ASC');
        return $queryBuilder->execute()->fetchAll();
    }


    public function insertPanier($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('paniers')
            ->values([
                'id' => '?',
                'quantite' => '?',
                'prix' => '?',
                'dateAjoutPanier' => '?',
                'user_id' => '?',
                'produit_id' => '?',
                'commande_id' => '?'
            ])
            ->setParameter(0, $donnees['nom'])
            ->setParameter(1, $donnees['quantite'])
            ->setParameter(2, $donnees['prix'])
            ->setParameter(3, $donnees['dataAjoutPanier'])
            ->setParameter(4, $donnees['user_id'])
            ->setParameter(5, $donnees['produit_id'])
            ->setParameter(6, $donnees['commade_id'])
        ;
        return $queryBuilder->execute();
    }

    function getPanier($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('*')
            ->from('paniers')
            ->where('id= :id')
            ->setParameter('id', $id);
        return $queryBuilder->execute()->fetch();
    }

    function getPanierFromProduit($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('*')
            ->from('paniers')
            ->where('produit_id= :id')
            ->setParameter('id', $id);
        return $queryBuilder->execute()->fetch();
    }

    public function updateProduit($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('paniers')
            ->set('quantite','?')
            ->set('prix','?')
            ->set('dateAjoutPanier','?')
            ->set('user_id','?')
            ->set('produit_id','?')
            ->set('commande_id','?')
            ->where('id= ?')
            ->setParameter(0, $donnees['quantite'])
            ->setParameter(1, $donnees['prix'])
            ->setParameter(2, $donnees['dateAjoutPanier'])
            ->setParameter(3, $donnees['user_id'])
            ->setParameter(4, $donnees['produit_id'])
            ->setParameter(5, $donnees['commande_id'])
            ->setParameter(6, $donnees['id']);

        return $queryBuilder->execute();
    }

    public function deleteProduit($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('paniers')
            ->where('produit_id = :id')
            ->setParameter('id',(int)$id)
        ;
        return $queryBuilder->execute();
    }

    public function incrementStockPanier($id_produit){
        $panier=$this->getPanierFromProduit($id_produit);

        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('paniers')
            ->set('quantite','?')
            ->where('produit_id= ?')
            ->setParameter(0,$panier["quantite"]+1)
            ->setParameter(1,$id_produit)
        ;
        return $queryBuilder->execute();
    }

    public function decrementStockPanier($id_produit){
        $panier=$this->getPanierFromProduit($id_produit);

        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('paniers')
            ->set('quantite','?')
            ->where('produit_id= ?')
            ->setParameter(0,$panier["quantite"]-1)
            ->setParameter(1,$id_produit)
        ;
        return $queryBuilder->execute();
    }


}