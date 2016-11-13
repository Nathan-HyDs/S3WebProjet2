<?php
/**
 * Created by PhpStorm.
 * User: hyds
 * Date: 11/11/16
 * Time: 23:59
 */

namespace App\Controller;

use App\Model\ProduitModel;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0


class PanierController implements ControllerProviderInterface
{

    private $produitModel;

    public function acceuil(Application $app)
    {
        $this->produitModel=new ProduitModel($app);
        $data=$this->produitModel->getAllProduits();
        return $app["twig"]->render('frontOff\frontOFFICE.html.twig',['data'=>$data]);
    }

    public function insertPanier(Application $app){

        return null;
    }

    public function connect(Application $app)
    {
        $index = $app['controllers_factory'];
        $index->match("/acceuil", 'App\Controller\PanierController::acceuil')->bind('panier.index');
        return $index;
    }
}