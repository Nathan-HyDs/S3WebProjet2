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
        if($app['session']->get('droit')!='DROITclient')
            return $app->redirect($app["url_generator"]->generate("user.login"));

        $this->userModel = new UserModel($app);
        $id = $app['session']->get('user_id');
        $users = $this->userModel->getUser($id);
        return $app["twig"]->render('frontOff/InfoUser.html.twig', ['data' => $users]);
    }

    public function  validFormEditPassword(Application $app){
        if($app['session']->get('droit')!='DROITclient')
            return $app->redirect($app["url_generator"]->generate("user.login"));

        if(isset($_POST['motdepasse']) && isset($_POST['motdepasse2'])){
            $donnees=[
                'motdepasse'=>htmlspecialchars($_POST['motdepasse']),
                'motdepasse2'=>htmlspecialchars($_POST['motdepasse2']),
                'id'=>$app['session']->get('user_id')
            ];
            if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['motdepasse']))) $erreurs['motdepasse']='motdepasse composé de 2 lettres minimum';
            if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['motdepasse2']))) $erreurs['motdepasse2']='motdepasse2 composé de 2 lettres minimum';

            $id = $app['session']->get('user_id');

            if (strcmp($donnees['motdepasse'],$donnees['motdepasse2'])==0) {
                if (empty($erreurs)){
                    $this->userModel = new UserModel($app);
                    $this->userModel->updatePasswordUsers($donnees);
                    $users = $this->userModel->getUser($id);
                    $success["motdepasse"] = "Modification mot de passe réussite";
                    return $app["twig"]->render('frontOff/InfoUser.html.twig', ['data' => $users, 'success' => $success]);
                }
            }
            $this->userModel = new UserModel($app);
            $users = $this->userModel->getUser($id);
            $error["motdepasse"]="Probleme modification mot de passe";
            return $app["twig"]->render('frontOff/InfoUser.html.twig',['data' => $users, 'erreurs'=>$error]);


        }

    }

    public function validFormEdit(Application $app, Request $req) {
        if($app['session']->get('droit')!='DROITclient')
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
            if(! preg_match("/^[A-Za-z0-9 ]{5,}/",$donnees['adresse']))$erreurs['adresse']='adresse composé de 5 lettres minimum';
            $donnees['id']=$_POST['id'];
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
            $id = $app['session']->get('user_id');
            if (!empty($erreurs)) {
                $this->userModel = new UserModel($app);
                $users = $this->userModel->getUser($id);
                return $app["twig"]->render('frontOff/InfoUser.html.twig',['data' => $users, 'error'=>$erreurs]);
            }
            else
            {
                $this->userModel = new UserModel($app);
                $this->userModel->updateDonneesUsers($donnees);
                $users = $this->userModel->getUser($id);
                $success['motdepasse']="Modification réussite";
                return $app["twig"]->render('frontOff/InfoUser.html.twig',['data' => $users, 'success'=>$success]);
            }

        }
        else
            return $app->abort(404, 'error Pb id form edit');

    }

    public function showAllUsers(Application $app){
        if($app['session']->get('droit')!='DROITadmin')
            return $app->redirect($app["url_generator"]->generate("user.login"));

        $this->userModel = new UserModel($app);
        $users = $this->userModel->getUsers();
        return $app["twig"]->render('backOff/infoUsersAdmin.html.twig', ['users' => $users]);

    }

    public function deleteUser(Application $app, $id){
        if($app['session']->get('droit')!='DROITadmin')
            return $app->redirect($app["url_generator"]->generate("user.login"));

        $this->userModel = new UserModel($app);
        $this->userModel->suppUser($id);
        return $app->redirect($app["url_generator"]->generate("user.showAllUsers"));
    }

    public function upgradeUserIntoAdmin(Application $app, $id){
        if($app['session']->get('droit')!='DROITadmin')
            return $app->redirect($app["url_generator"]->generate("user.login"));

        $this->userModel = new UserModel($app);
        $this->userModel->upgradeAdmin($id);
        return $app->redirect($app["url_generator"]->generate("user.showAllUsers"));
    }

    public function addUser(Application $app){
        if($app['session']->get('droit')=='DROITadmin')
            return $app->redirect($app["url_generator"]->generate("accueil"));
        if($app['session']->get('droit')=='DROITclient')
            return $app->redirect($app["url_generator"]->generate("accueil"));

        return $app["twig"]->render('v_inscription.html.twig');

    }
    public function validAddUser(Application $app){
        if($app['session']->get('droit')=='DROITadmin')
            return $app->redirect($app["url_generator"]->generate("accueil"));
        if($app['session']->get('droit')=='DROITclient')
            return $app->redirect($app["url_generator"]->generate("accueil"));

        if (isset($_POST['email']) and isset($_POST['nom']) and isset($_POST['ville']) and isset($_POST['code_postal']) and isset($_POST['adresse'])) {
            $donnees = [
                'nom' => htmlspecialchars($_POST["nom"]),
                'ville' => htmlspecialchars($_POST["ville"]),
                'code_postal' => htmlspecialchars($_POST["code_postal"]),
                'adresse' => htmlspecialchars($_POST["adresse"]),
                'login'=>htmlspecialchars($_POST["login"]),
                'password'=>htmlspecialchars($_POST["password"]),
                'email'=>htmlspecialchars($_POST['email'])
            ];
        }


        if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['login']))) $erreurs['login']='login composé de 2 lettres minimum';
        if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['nom']))) $erreurs['nom']='nom composé de 2 lettres minimum';
        if (empty($donnees['password'])) $erreurs['password']='Mot de passe vide';
        if ((! preg_match("/^([0-9a-z'àâéèêôùûçÀÂÉÈÔÙÛÇ\s-]{1,50})$/",$donnees['adresse']))) $erreurs['adresse']='adresse composé de 2 lettres minimum';
        if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['ville']))) $erreurs['ville']='ville composé de 2 lettres minimum';
        if(! is_numeric($donnees['code_postal']))$erreurs['code_postal']='veuillez saisir une valeur numérique';
        if (!filter_var($donnees["email"], FILTER_VALIDATE_EMAIL)) {
            $erreurs["email"]= "Invalid email format";
        }

        if(!empty($erreurs)){
            return $app["twig"]->render('v_inscription.html.twig',["erreur"=>$erreurs, "donnees"=>$donnees]);
        }else{
            $this->userModel = new UserModel($app);
            $this->userModel->insertUser($donnees);
            return $app->redirect($app["url_generator"]->generate("accueil"));
        }

    }

    public function connect(Application $app) {
		$controllers = $app['controllers_factory'];

		$controllers->match('/', 'App\Controller\UserController::index')->bind('user.index');
        $controllers->match('/informations', 'App\Controller\UserController::moreInfoClient')->bind('user.moreInfoClient');
        $controllers->match('/showAllUsers', 'App\Controller\UserController::showAllUsers')->bind('user.showAllUsers');
        $controllers->match('/validFormEdit', 'App\Controller\UserController::validFormEdit')->bind('user.validFormEdit');
        $controllers->match('/validFormEditPassword', 'App\Controller\UserController::validFormEditPassword')->bind('user.validFormEditPassword');

        $controllers->get('/deleteUser/{id}', 'App\Controller\UserController::deleteUser')->bind('user.deleteUser')->assert('id', '\d+');
        $controllers->get('/upgradeUserIntoAdmin/{id}', 'App\Controller\UserController::upgradeUserIntoAdmin')->bind('user.upgradeUserIntoAdmin')->assert('id', '\d+');


        $controllers->get('/login', 'App\Controller\UserController::connexionUser')->bind('user.login');
		$controllers->post('/login', 'App\Controller\UserController::validFormConnexionUser')->bind('user.validFormlogin');

        $controllers->match('/addUser', 'App\Controller\UserController::addUser')->bind('user.addUser');
        $controllers->post('/validAddUser', 'App\Controller\UserController::validAddUser')->bind('user.validAddUser');


        $controllers->get('/logout', 'App\Controller\UserController::deconnexionSession')->bind('user.logout');
		return $controllers;
	}
}