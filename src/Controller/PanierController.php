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
use App\Model\CommandeModel;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0


class PanierController implements ControllerProviderInterface
{

    private $produitModel;
    private $panierModel;
    private $commandeModel;


    public function acceuil(Application $app)
    {
        if($app['session']->get('droit')!='DROITclient')
            return $app->redirect($app["url_generator"]->generate("user.login"));

        $id=$app['session']->get('user_id');
        $this->panierModel=new PanierModel($app);
        $this->produitModel=new ProduitModel($app);
        $data=$this->produitModel->getAllProduits();
        $panier=$this->panierModel->getAllPanier($id);
        $price=$this->panierModel->getPrixTotaleOfPanier($id);

        foreach ($data as $key=>$produit){
            $qt=$this->panierModel->getPanierFromProduitAndUser($produit['id'], $id);
            if(!empty($qt))
                $data[$key]['stock']=$data[$key]['stock']-$qt['quantite'];
        }

        if(!empty($app["session"]->get("donnees"))){
            $donnees=$app["session"]->get("donnees");
            $donnees["pricePanier"]=$price["prixTot"];
            $app["session"]->set('donnees',null);
            return $app["twig"]->render('frontOff\frontOFFICE.html.twig',['data'=>$data , 'panier'=>$panier , 'donnees'=>$donnees]);

        }
        else{
            $donnees["pricePanier"]=$price["prixTot"];
            return $app["twig"]->render('frontOff\frontOFFICE.html.twig',['data'=>$data , 'panier'=>$panier,'donnees'=>$donnees]);
        }
    }

    public function insert(Application $app){
        if($app['session']->get('droit')!='DROITclient')
            return $app->redirect($app["url_generator"]->generate("user.login"));

        $id_client=$app['session']->get('user_id');

        $donnees=[
            "id"=>htmlentities($_POST["id"]),
            "quantite"=>htmlentities($_POST["quantite"]),
        ];


        $this->panierModel=new PanierModel($app);
        $this->produitModel=new ProduitModel($app);


        $produit = $this->produitModel->getProduit($donnees['id']);

        $panier = $this->panierModel->getPanierFromProduitAndUser($donnees['id'],$id_client);

        if(is_numeric($donnees["quantite"]) and $donnees["quantite"]>0){
            if($produit['stock']-$panier['quantite']>=$donnees['quantite']) {
                if (empty($panier)) {
                    $DonnePanier = [
                        'id' => null,
                        'quantite' => $donnees['quantite'],
                        'prix' => $produit["prix"],
                        'user_id' => $id_client,
                        'produit_id' => $donnees["id"],
                        'commande_id' => null
                    ];
                    //$this->produitModel->supprXStockProduit($produit["id"],$donnees['quantite']);
                    $this->panierModel->insertPanier($DonnePanier);
                }
                else if($donnees['quantite']==1){
                    $this->panierModel->incrementStockPanier($produit["id"],$id_client);
                    //$this->produitModel->decrementeStockProduit($produit["id"]);
                }
                else{
                    //$this->produitModel->supprXStockProduit($produit["id"],$donnees['quantite']);
                    $this->panierModel->addXStockPanier($produit["id"],$id_client,$donnees['quantite']);
                }
            }
            else{
                $donnees['error']="Ce produit n'est pas en stock en assez grande quantitée !";
            }
        }
        else{
            $donnees["error"]="Erreur de saisie";
        }



        $app["session"]->set("donnees",$donnees);

        return $app->redirect($app["url_generator"]->generate("panier.index"));

    }

    public function delete(Application $app){
        if($app['session']->get('droit')!='DROITclient')
            return $app->redirect($app["url_generator"]->generate("user.login"));
        $id_client=$app['session']->get('user_id');

        $donnees=[
            "id"=>htmlentities($_POST["id"]),
            "quantite"=>htmlentities($_POST["quantite"]),
        ];

        $this->panierModel=new PanierModel($app);
        $this->produitModel=new ProduitModel($app);

        $data=$this->produitModel->getAllProduits();

        $produit = $this->produitModel->getProduit($donnees['id']);
        $panier = $this->panierModel->getPanierFromProduitAndUser($donnees['id'],$id_client);

        if(is_numeric($donnees["quantite"]) and $donnees["quantite"]>0) {
            if ($panier["quantite"] == $donnees['quantite']) {
                $this->panierModel->deleteProduit($donnees['id'], $id_client);
 //               $this->produitModel->addXStockProduit($donnees['id'], $panier["quantite"]);
            } else {
                if ($donnees['quantite'] == 1) {
                    $this->panierModel->decrementStockPanier($produit["id"], $id_client);
                    //$this->produitModel->incrementeStockProduit($produit["id"]);
                } else if ($panier["quantite"] > $donnees['quantite']) {
                    $this->panierModel->deleteXStockPanier($produit["id"], $id_client, $donnees['quantite']);
                    //$this->produitModel->addXStockProduit($produit["id"], $donnees['quantite']);
                } else {
                    $donnees["error"] = "Attention il n'y a pas autant de produit à enlever !";
                }
            }
        }
        else{
            $donnees["error"]="Erreur de saisie";
        }

        $app["session"]->set("donnees",$donnees);

        return $app->redirect($app["url_generator"]->generate("panier.index"));
    }

    public function newCommande(Application $app){
        /*
         * $conn->beginTransaction();
         * $requestSQL=$conn->prepare('REQUETE INSERTION COMMAND')
         * $requestSQL=execute([$user_id,$prix,1])
         * $lastinsertid=$conn->LastInsertId();
         *
         * $requestSQL=$conn->prepare('UPDATE PANIERS');
         * $requestSQL->execute([$lastinsertid,$user_id]);
         * $conn->commit();
         *
         */
        if($app['session']->get('droit')!='DROITclient')
            return $app->redirect($app["url_generator"]->generate("user.login"));
        $test=true;

        $id_client=$app['session']->get('user_id');

        $this->panierModel=new PanierModel($app);
        $this->produitModel=new ProduitModel($app);
        $this->commandeModel=new CommandeModel($app);

        $paniers=$this->panierModel->getAllPanier($id_client);


        $prixTotale=$this->panierModel->getPrixTotaleOfPanier($id_client)['prixTot'];

        $donnees=[
            "id"=>null,
            "user_id"=>$id_client,
            "prix"=>0,
            "date_achat"=>null,
            "etat_id"=>1
        ];

        foreach ($paniers as $panier){
            $produit = $this->produitModel->getProduit($panier['produit_id']);
            if($panier['quantite']>$produit['stock'])
                $test=false;
        }

        if($test){

            $this->commandeModel->createCommande($donnees);
            $commande=$this->commandeModel->findCommadeWithoutPrice($id_client);

            $this->commandeModel->setupPriceCommande($commande['id'],$prixTotale);

            foreach ($paniers as $panier){
                $this->produitModel->supprXStockProduit($panier["produit_id"],$panier["quantite"]);
                $this->panierModel->setCommande($panier['id'],$commande['id']);
            }
        }
        else{
            $donnees["error"]="Oops ! Il n'y a plus le bon stock pour l'un de vos produits";
        }

        $app["session"]->set("donnees",$donnees);
        return $app->redirect($app["url_generator"]->generate("panier.index"));
    }

    public function connect(Application $app)
    {
        $index = $app['controllers_factory'];
        $index->match("/acceuil", 'App\Controller\PanierController::acceuil')->bind('panier.index');

        $index->match("/newCommande", 'App\Controller\PanierController::newCommande')->bind('panier.newCommande');
        $index->post("/insert", 'App\Controller\PanierController::insert')->bind('panier.insert');
        $index->post("/delete", 'App\Controller\PanierController::delete')->bind('panier.delete');

        return $index;
    }
}