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
        $this->produitModel=new ProduitModel($app);
        $data=$this->produitModel->getAllProduits();
        return $app["twig"]->render('frontOff\frontOFFICE.html.twig',['data'=>$data]);
    }

    public function insert(Application $app,$id){
        $this->panierModel=new PanierModel($app);
        $this->produitModel=new ProduitModel($app);

        $data=$this->produitModel->getAllProduits();

        $produit = $this->produitModel->getProduit($id);

        $panier = $this->panierModel->getPanierFromProduit($id);

        if(empty($panier)){
            $this->panierModel->insert($produit);
        }

        return $app["twig"]->render('frontOff\frontOFFICE.html.twig',['data'=>$data]);
    }

    public function connect(Application $app)
    {
        $index = $app['controllers_factory'];
        $index->match("/acceuil", 'App\Controller\PanierController::acceuil')->bind('panier.index');
        $index->get("/insert/{id}", 'App\Controller\PanierController::insert')->bind('panier.insert')->assert('id', '\d+');

        return $index;
    }
}