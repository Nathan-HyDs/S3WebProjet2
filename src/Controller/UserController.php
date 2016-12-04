<?php
namespace App\Controller;

use App\Model\UserModel;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0

use Symfony\Component\HttpFoundation\Request;   // pour utiliser request

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