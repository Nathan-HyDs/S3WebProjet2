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

        if ($app['session']->get('droit') == 'DROITclient'){
            $commandes = $this->commandeModel->getAllCommandesFromClient($id);
            return $app["twig"]->render("frontOff/showCommandeFrontOffice.html.twig",['data'=>$commandes]);
        }
        if ($app['session']->get('droit') == 'DROITadmin'){
            $commandes = $this->commandeModel->getAllCommandes();
            return $app["twig"]->render("backOff/showCommandeFrontOffice.html.twig",['data'=>$commandes]);
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
/*

    public function edit(Application $app, $id) {
        $this->commandeModel = new CommandeModel($app);
        $commande = $this->commandeModel->getAllCommandes();
        return $app["twig"]->render('backOff/Commande/edit.html.twig',['commande'=>$commande]);
    }

    public function validFormEdit(Application $app, Request $req) {
        // var_dump($app['request']->attributes);
        if (isset($_POST['id']) && isset($_POST['user_id']) and isset($_POST['prix']) and isset($_POST['date_achat']) and isset($_POST['etat_id'])) {
            $donnees = [
                'id' => htmlspecialchars($_POST['id']),                    // echapper les entrées
                'user_id' => htmlspecialchars($req->get('user_id')),  //$app['request']-> ne focntionne plus
                'prix' => htmlspecialchars($req->get('prix')),
                'date_achat' => htmlspecialchars($req->get('date_achat')),
                'etat_id' => htmlspecialchars($req->get('etat_id'))
            ];
        }
        else
            return $app->abort(404, 'error Pb id form edit');

    }
*/
    public function connect(Application $app) {  //http://silex.sensiolabs.org/doc/providers.html#controller-providers
        $controllers = $app['controllers_factory'];

        $controllers->match('/', 'App\Controller\commandeController::index')->bind('commande.index');
        $controllers->match('/show', 'App\Controller\commandeController::show')->bind('commande.show');

        $controllers->get('/validCommande/{id}', 'App\Controller\commandeController::validCommande')->bind('commande.validCommande')->assert('id', '\d+');
        $controllers->get('/delete/{id}', 'App\Controller\commandeController::delete')->bind('commande.delete')->assert('id', '\d+');


        return $controllers;
    }
}
