<?php
namespace App\Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0

use App\Model\ProduitModel;
use App\Model\PanierModel;
use App\Model\CommandeModel;


class CommandeController implements ControllerProviderInterface
{
    private $produitModel;
    private $commandeModel;
    private $panierModel;


    public function initModel(Application $app){  //  ne fonctionne pas dans le const
        $this->produitModel = new ProduitModel($app);
        $this->commandeModel = new CommandeModel($app);
    }

    public function index(Application $app) {
        return $this->show($app);
    }

    public function show(Application $app) {
        $id=$app['session']->get('user_id');
        $this->commandeModel = new CommandeModel($app);
        $this->panierModel = new PanierModel($app);


        if ($app['session']->get('droit') == 'DROITclient'){
            $commandes = $this->commandeModel->getAllCommandesFromClient($id);
            return $app["twig"]->render("frontOff/showCommandeFrontOffice.html.twig",['data'=>$commandes]);
        }
        if ($app['session']->get('droit') == 'DROITadmin'){
            $tabPaniers=[];
            $commandes = $this->commandeModel->getAllCommandes();
            $tabPaniers=$this->panierModel->getAllPaniers();
            return $app["twig"]->render("backOff/showCommandeFrontOffice.html.twig",['data'=>$commandes , 'paniers'=>$tabPaniers]);
        }

    }


    public function validCommande(Application $app,$id){
        if($app['session']->get('droit')!='DROITadmin')
            return $app->redirect($app["url_generator"]->generate("user.login"));

        $this->commandeModel=new CommandeModel($app);
        $this->commandeModel->validCommande($id);
        return $app->redirect($app["url_generator"]->generate("commande.show"));
    }


    public function delete(Application $app,$id) {
        if($app['session']->get('droit')!='DROITadmin')
            return $app->redirect($app["url_generator"]->generate("user.login"));

        $this->commandeModel = new CommandeModel($app);
        $this->panierModel = new PanierModel($app);

        $this->panierModel->deletePanierByCommande($id);
        $this->commandeModel->deleteCommande($id);

        return $app->redirect($app["url_generator"]->generate("commande.show"));

    }

    public function connect(Application $app) {  //http://silex.sensiolabs.org/doc/providers.html#controller-providers
        $controllers = $app['controllers_factory'];

        $controllers->match('/', 'App\Controller\commandeController::index')->bind('commande.index');
        $controllers->match('/show', 'App\Controller\commandeController::show')->bind('commande.show');

        $controllers->get('/validCommande/{id}', 'App\Controller\commandeController::validCommande')->bind('commande.validCommande')->assert('id', '\d+');
        $controllers->get('/delete/{id}', 'App\Controller\commandeController::delete')->bind('commande.delete')->assert('id', '\d+');


        return $controllers;
    }
}
