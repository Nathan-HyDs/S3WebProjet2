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

    public function insert(Application $app,$id){
        $id_client=3;

        $this->panierModel=new PanierModel($app);
        $this->produitModel=new ProduitModel($app);

        $data=$this->produitModel->getAllProduits();

        $produit = $this->produitModel->getProduit($id);

        $panier = $this->panierModel->getPanierFromProduit($id);

        if($produit['stock']>=1) {
            if (empty($panier)) {
                $DonnePanier = [
                    'id' => null,
                    'quantite' => 1,
                    'prix' => $produit["prix"],
                    'user_id' => $id_client,
                    'produit_id' => $produit["id"],
                    'commande_id' => 1
                ];

                $this->panierModel->insertPanier($DonnePanier);
            } else {
                $this->panierModel->incrementStockPanier($produit["id"]);
                $this->produitModel->decrementeStockProduit($produit["id"]);
            }
        }

        return $app->redirect($app["url_generator"]->generate("panier.index"));
    }

    public function delete(Application $app,$id){
        $id_client=3;

        $this->panierModel=new PanierModel($app);
        $this->produitModel=new ProduitModel($app);

        $data=$this->produitModel->getAllProduits();

        $produit = $this->produitModel->getProduit($id);

        $panier = $this->panierModel->getPanierFromProduit($id);

        if($panier["quantite"]==1){
            $this->panierModel->deleteProduit($id);
        }else{
            $this->panierModel->decrementStockPanier($produit["id"]);
            $this->produitModel->incrementeStockProduit($produit["id"]);
        }


        return $app->redirect($app["url_generator"]->generate("panier.index"));
    }

    public function connect(Application $app)
    {
        $index = $app['controllers_factory'];
        $index->match("/acceuil", 'App\Controller\PanierController::acceuil')->bind('panier.index');

        $index->get("/insert/{id}", 'App\Controller\PanierController::insert')->bind('panier.insert')->assert('id', '\d+');
        $index->get("/delete/{id}", 'App\Controller\PanierController::delete')->bind('panier.delete')->assert('id', '\d+');

        return $index;
    }
}