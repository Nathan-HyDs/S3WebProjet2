<?php

namespace App\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;

class ProduitModel {

    private $db;

    public function __construct(Application $app) {
        $this->db = $app['db'];
    }
    // http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/query-builder.html#join-clauses
    public function getAllProduits() {
//        $sql = "SELECT p.id, t.libelle, p.nom, p.prix, p.photo
//            FROM produits as p,typeProduits as t
//            WHERE p.typeProduit_id=t.id ORDER BY p.nom;";
//        $req = $this->db->query($sql);
//        return $req->fetchAll();
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id','p.nom','p.prix','p.photo','t.libelle','p.stock')
            ->from('produits', 'p')
            ->innerJoin('p', 'typeProduits', 't', 'p.typeProduit_id=t.id')
            ->addOrderBy('t.id ', 'ASC')
            ->addOrderBy('p.nom', 'ASC');
        return $queryBuilder->execute()->fetchAll();

    }

    public function insertProduit($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('produits')
            ->values([
                'nom' => '?',
                'typeProduit_id' => '?',
                'prix' => '?',
                'photo' => '?'
            ])
            ->setParameter(0, $donnees['nom'])
            ->setParameter(1, $donnees['typeProduit_id'])
            ->setParameter(2, $donnees['prix'])
            ->setParameter(3, $donnees['photo'])
        ;
        return $queryBuilder->execute();
    }

    function getProduit($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('id', 'typeProduit_id', 'nom', 'prix', 'photo','stock')
            ->from('produits')
            ->where('id= :id')
            ->setParameter('id', $id);
        return $queryBuilder->execute()->fetch();
    }

    public function updateProduit($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('produits')
            ->set('nom', '?')
            ->set('typeProduit_id','?')
            ->set('prix','?')
            ->set('photo','?')
            ->where('id= ?')
            ->setParameter(0, $donnees['nom'])
            ->setParameter(1, $donnees['typeProduit_id'])
            ->setParameter(2, $donnees['prix'])
            ->setParameter(3, $donnees['photo'])
            ->setParameter(4, $donnees['id']);
        return $queryBuilder->execute();
    }

    public function deleteProduit($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('produits')
            ->where('id = :id')
            ->setParameter('id',(int)$id)
        ;
        return $queryBuilder->execute();
    }


    public function decrementeStockProduit($id){
        $produit=$this->getProduit($id);


        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('produits')
            ->set('stock','?')
            ->where('id = ?')
            ->setParameter(0, $produit["stock"]-1)
            ->setParameter(1,$id)
        ;
        return $queryBuilder->execute();
    }

    public function incrementeStockProduit($id){
        $produit=$this->getProduit($id);


        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('produits')
            ->set('stock','?')
            ->where('id = ?')
            ->setParameter(0, $produit["stock"]+1)
            ->setParameter(1,$id)
        ;
        return $queryBuilder->execute();
    }

    public function supprXStockProduit($id,$nb){
        $produit=$this->getProduit($id);


        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('produits')
            ->set('stock','?')
            ->where('id = ?')
            ->setParameter(0, $produit["stock"]-$nb)
            ->setParameter(1,$id)
        ;
        return $queryBuilder->execute();
    }

    public function addXStockProduit($id,$nb){
        $produit=$this->getProduit($id);


        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('produits')
            ->set('stock','?')
            ->where('id = ?')
            ->setParameter(0, $produit["stock"]+$nb)
            ->setParameter(1,$id)
        ;
        return $queryBuilder->execute();
    }

    public function getProduitFromType($id_type)
    {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id','p.nom','p.prix','p.photo','t.libelle','p.stock')
            ->from('produits', 'p')
            ->innerJoin('p', 'typeProduits', 't', 'p.typeProduit_id=t.id')
            ->where('t.id='.$id_type )
            ->addOrderBy('t.id ', 'ASC')
            ->addOrderBy('p.nom', 'ASC');

        return $queryBuilder->execute()->fetchAll();
    }
}