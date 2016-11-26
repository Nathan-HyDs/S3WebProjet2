<?php
/**
 * Created by PhpStorm.
 * User: hyds
 * Date: 11/11/16
 * Time: 23:59
 */

namespace App\Controller;

use App\Model\ProduitModel;
use App\Model\PanierModel;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0


class PanierController implements ControllerProviderInterface
{

    private $produitModel;
    private $panierModel;

    public function acceuil(Application $app)
    {
        $id=3;
        $this->panierModel=new PanierModel($app);
        $this->produitModel=new ProduitModel($app);
        $data=$this->produitModel->getAllProduits();
        $panier=$this->panierModel->getAllPanier($id);
        return $app["twig"]->render('frontOff\frontOFFICE.html.twig',['data'=>$data , 'panier'=>$panier]);
    }

    public function insert(Application $app){
        $id_client=3;

        $donnees=[
            "id"=>htmlentities($_POST["id"]),
            "quantite"=>htmlentities($_POST["quantite"]),
        ];

        $this->panierModel=new PanierModel($app);
        $this->produitModel=new ProduitModel($app);

        $data=$this->produitModel->getAllProduits();

        $produit = $this->produitModel->getProduit($donnees['id']);

        $panier = $this->panierModel->getPanierFromProduit($donnees['id']);

        if($produit['stock']>=$donnees['quantite']) {
            if (empty($panier)) {
                $DonnePanier = [
                    'id' => null,
                    'quantite' => $donnees['quantite'],
                    'prix' => $produit["prix"],
                    'user_id' => $id_client,
                    'produit_id' => $donnees["id"],
                    'commande_id' => 1
                ];
                $this->produitModel->supprXStockProduit($produit["id"],$donnees['quantite']);
                $this->panierModel->insertPanier($DonnePanier);
                if($donnees['quantite']==1){
                    $this->panierModel->incrementStockPanier($produit["id"]);
                    $this->produitModel->decrementeStockProduit($produit["id"]);
                }
                else if($donnees['quantite']){
                    $this->produitModel->supprXStockProduit($produit["id"],$donnees['quantite']);
                    $this->panierModel->addXStockPanier($produit["id"],$donnees['quantite']);
                }
            }
        }
        else{
            $donnees['error']="Ce produit n'est pas en stock en assez grande quantitée !";
        }


        $data=$this->produitModel->getAllProduits();
        $panier=$this->panierModel->getAllPanier($id_client);

        return $app["twig"]->render('frontOff\frontOFFICE.html.twig',['data'=>$data , 'panier'=>$panier, 'donnees'=>$donnees]);
    }

    public function delete(Application $app){
        $id_client=3;

        $donnees=[
            "id"=>htmlentities($_POST["id"]),
            "quantite"=>htmlentities($_POST["quantite"]),
        ];

        $this->panierModel=new PanierModel($app);
        $this->produitModel=new ProduitModel($app);

        $data=$this->produitModel->getAllProduits();

        $produit = $this->produitModel->getProduit($donnees['id']);

        $panier = $this->panierModel->getPanierFromProduit($donnees['id']);

        if($panier["quantite"]==$donnees['quantite']){
            $this->panierModel->deleteProduit($donnees['id']);
            $this->produitModel->addXStockProduit($donnees['id'],$panier["quantite"]);
        }else{
            if($donnees['quantite']==1){
                $this->panierModel->decrementStockPanier($produit["id"]);
                $this->produitModel->incrementeStockProduit($produit["id"]);
            }
            else if($panier["quantite"]>$donnees['quantite']){
                $this->panierModel->deleteXStockPanier($produit["id"],$donnees['quantite']);
                $this->produitModel->addXStockProduit($produit["id"],$donnees['quantite']);
            }
            else{
                $donnees["error"]="Attention il n'y a pas autant de produit à enlever !";
            }
        }

        $data=$this->produitModel->getAllProduits();
        $panier=$this->panierModel->getAllPanier($id_client);

        return $app["twig"]->render('frontOff\frontOFFICE.html.twig',['data'=>$data , 'panier'=>$panier, 'donnees'=>$donnees]);
    }

    public function connect(Application $app)
    {
        $index = $app['controllers_factory'];
        $index->match("/acceuil", 'App\Controller\PanierController::acceuil')->bind('panier.index');

        $index->post("/insert", 'App\Controller\PanierController::insert')->bind('panier.insert');
        $index->post("/delete", 'App\Controller\PanierController::delete')->bind('panier.delete');

        return $index;
    }
}