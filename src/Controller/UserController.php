<?php
namespace App\Controller;

use App\Model\UserModel;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0

use Symfony\Component\HttpFoundation\Request;   // pour utiliser request

use Symfony\Component\Validator\Constraints as Assert;

class UserController implements ControllerProviderInterface {

	private $userModel;

	public function index(Application $app) {
		return $this->connexionUser($app);
	}

	public function connexionUser(Application $app)
	{
		return $app["twig"]->render('v_session_connexion.html.twig');
	}

	public function validFormConnexionUser(Application $app, Request $req)
	{

		$app['session']->clear();
		$donnees['login']=$req->get('login');
		$donnees['password']=$req->get('password');

		$this->userModel = new UserModel($app);
		$data=$this->userModel->verif_login_mdp_Utilisateur($donnees['login'],$donnees['password']);

		if($data != NULL)
		{
			$app['session']->set('droit', $data['droit']);  //dans twig {{ app.session.get('droit') }}
			$app['session']->set('login', $data['login']);
			$app['session']->set('logged', 1);
			$app['session']->set('user_id', $data['id']);
			return $app->redirect($app["url_generator"]->generate("accueil"));
		}
		else
		{
			$app['session']->set('erreur','mot de passe ou login incorrect');
			return $app["twig"]->render('v_session_connexion.html.twig');
		}
	}
	public function deconnexionSession(Application $app)
	{
		$app['session']->clear();
		$app['session']->getFlashBag()->add('msg', 'vous êtes déconnecté');
		return $app->redirect($app["url_generator"]->generate("accueil"));
	}

	public function moreInfoClient(Application $app)
    {
        $this->userModel = new UserModel($app);
        $id = $app['session']->get('user_id');
        $users = $this->userModel->getUser($id);
        return $app["twig"]->render('frontOff/InfoUser.html.twig', ['data' => $users]);
    }

    public function edit(Application $app , $user_id) {
        if($app['session']->get('droit')!='DROITadmin')
            return $app->redirect($app["url_generator"]->generate("user.login"));

        $this->userModel = new UserModel($app);
        $users = $this->userModel->getUser($user_id);
        return $app["twig"]->render('frontOff/InfoUser.html.twig', ['data' => $users]);
    }

    public function validFormEdit(Application $app, Request $req) {
        if($app['session']->get('droit')!='DROITadmin')
            return $app->redirect($app["url_generator"]->generate("user.login"));

        if (isset($_POST['nom']) and isset($_POST['ville']) and isset($_POST['code_postal']) and isset($_POST['adresse'])) {
            $donnees = [
                'nom' => htmlspecialchars($req->get('nom')),
                'ville' => htmlspecialchars($req->get('ville')),
                'code_postal' => htmlspecialchars($req->get('code_postal')),
                'adresse' => $app->escape($req->get('adresse')),
            ];
            if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['nom']))) $erreurs['nom']='nom composé de 2 lettres minimum';
            if(! preg_match("/^[A-Za-z ]{3,}/",$donnees['ville']))$erreurs['ville']='nom de ville composé de 3 lettres minimum';
            if(! is_numeric($donnees['code_postal']))$erreurs['code_postal']='saisir une valeur numérique';
            if(! preg_match("/^[A-Za-z ]{5,}/",$donnees['adresse']))$erreurs['adresse']='adresse composé de 5 lettres minimum';
            $contraintes = new Assert\Collection(
                [
                    'id' => [new Assert\NotBlank(),new Assert\Type('digit')],
                    'nom' => [
                        new Assert\NotBlank(['message'=>'saisir une valeur']),
                        new Assert\Length(['min'=>2, 'minMessage'=>"Le nom doit faire au moins {{ limit }} caractères."])
                    ],
                    'ville' => [
                        new Assert\NotBlank(['message'=>'saisir une valeur']),
                        new Assert\Length(['min'=>3, 'minMessage'=>"Le nom de la ville doit faire au moins {{ limit }} caractères."])
                    ],
                    'code_postal' => new Assert\Type(array(
                        'type'    => 'numeric',
                        'message' => 'La valeur {{ value }} n\'est pas valide, le type est {{ type }}.',
                    )),
                    'adresse' => [
                        new Assert\NotBlank(['message'=>'saisir une valeur']),
                        new Assert\Length(['min'=>5, 'minMessage'=>"L'adresse doit faire au moins {{ limit }} caractères."])
                    ]
                ]);
            $errors = $app['validator']->validate($donnees,$contraintes);
            if (count($errors) > 0) {
                $this->userModel = new UserModel($app);
                $id = $app['session']->get('user_id');
                $users = $this->userModel->getUser($id);
                return $app["twig"]->render('frontOff/InfoUser.html.twig',['data' => $users]);
            }
            else
            {
                $this->UserModel = new UserModel($app);
                $this->UserModel->updateDonneesUsers($donnees);
                return $app->redirect($app["url_generator"]->generate("produit.index"));
            }

        }
        else
            return $app->abort(404, 'error Pb id form edit');

    }

	public function connect(Application $app) {
		$controllers = $app['controllers_factory'];

		$controllers->match('/', 'App\Controller\UserController::index')->bind('user.index');
        $controllers->match('/informations', 'App\Controller\UserController::moreInfoClient')->bind('user.moreInfoClient');
		$controllers->get('/login', 'App\Controller\UserController::connexionUser')->bind('user.login');
		$controllers->post('/login', 'App\Controller\UserController::validFormConnexionUser')->bind('user.validFormlogin');
		$controllers->get('/logout', 'App\Controller\UserController::deconnexionSession')->bind('user.logout');
		return $controllers;
	}
}