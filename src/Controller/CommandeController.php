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


    public function initModel(Application $app){  //  ne fonctionne pas dans le const
        $this->produitModel = new ProduitModel($app);
        $this->commandeModel = new CommandeModel($app);
    }

    public function index(Application $app) {
        return $this->show($app);
    }

    public function show(Application $app) {
        $this->commandeModel = new CommandeModel($app);
        $commandes = $this->commandeModel->getAllCommandes();
        return $app["twig"]->render('backOff/Commande/show.html.twig',['commandes'=>$commandes]);
    }

    public function delete(Application $app) {
        $this->commandeModel = new CommandeModel($app);
        $commandes = $this->commandeModel->getAllCommandes();
        return $app["twig"]->render('backOff/Commande/delete.html.twig',['commandes'=>$commandes]);
    }

    public function validFormDelete(Application $app, Request $req) {
        $id=$app->escape($req->get('id'));
        if (is_numeric($id)) {
            $this->commandeModel = new CommandeModel($app);
            $this->commandeModel->deleteCommande($id);
            return $app->redirect($app["url_generator"]->generate("produit.index"));
        }
        else
            return $app->abort(404, 'error Pb id form Delete');
    }


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

    public function connect(Application $app) {  //http://silex.sensiolabs.org/doc/providers.html#controller-providers
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'App\Controller\produitController::index')->bind('produit.index');
        $controllers->get('/show', 'App\Controller\produitController::show')->bind('produit.show');

        $controllers->get('/add', 'App\Controller\produitController::add')->bind('produit.add');
        $controllers->post('/add', 'App\Controller\produitController::validFormAdd')->bind('produit.validFormAdd');

        $controllers->get('/delete/{id}', 'App\Controller\produitController::delete')->bind('produit.delete')->assert('id', '\d+');
        $controllers->delete('/delete', 'App\Controller\produitController::validFormDelete')->bind('produit.validFormDelete');

        $controllers->get('/edit/{id}', 'App\Controller\produitController::edit')->bind('produit.edit')->assert('id', '\d+');
        $controllers->put('/edit', 'App\Controller\produitController::validFormEdit')->bind('produit.validFormEdit');

        $controllers->get('/edit/{id}', 'App\Controller\commandeController::edit')->bind('produit.edit')->assert('id', '\d+');
        $controllers->put('/edit', 'App\Controller\commandeController::validFormEdit')->bind('produit.validFormEdit');

        return $controllers;
    }
}
