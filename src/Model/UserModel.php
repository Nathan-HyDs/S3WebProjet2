<?php
namespace App\Model;

use Silex\Application;
use Doctrine\DBAL\Query\QueryBuilder;;

class UserModel {

	private $db;

	public function __construct(Application $app) {
		$this->db = $app['db'];
	}

	public function verif_login_mdp_Utilisateur($login,$mdp){
		$sql = "SELECT id,login,password,droit FROM users WHERE login = ? AND password = ?";
		$res=$this->db->executeQuery($sql,[$login,$mdp]);   //md5($mdp);
		if($res->rowCount()==1)
			return $res->fetch();
		else
			return false;
	}

    public function updateDonneesUsers($donnees ) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('users')
            ->set('nom', '?')
            ->set('ville','?')
            ->set('code_postal','?')
            ->set('adresse','?')
            ->where('id = ?')
            ->setParameter(0, $donnees['nom'])
            ->setParameter(1, $donnees['ville'])
            ->setParameter(2, $donnees['code_postal'])
            ->setParameter(3, $donnees['adresse'])
            ->setParameter(4, $donnees['id']);
        return $queryBuilder->execute();
    }

    public function updatePasswordUsers($donnees ) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('users')
            ->set('password', '?')
            ->where('id = ?')
            ->setParameter(0, $donnees['motdepasse'])
            ->setParameter(1, $donnees['id']);
        return $queryBuilder->execute();
    }


    public function getUser($user_id) {
		$queryBuilder = new QueryBuilder($this->db);
		$queryBuilder
			->select('*')
			->from('users')
			->where('id = :idUser')
			->setParameter('idUser', $user_id);
		return $queryBuilder->execute()->fetch();

	}

	public function getUsers(){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('*')
            ->from('users');
        return $queryBuilder->execute()->fetchAll();
    }

    public function suppUser($user_id){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('users')
            ->where('id = ?')
            ->setParameter(0,$user_id);
        return $queryBuilder->execute();
    }

    public function upgradeAdmin($user_id){

        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('users')
            ->set('droit', '\'DROITadmin\'')
            ->where('id = ?')
            ->setParameter(0,$user_id);
        return $queryBuilder->execute();
    }

    public function insertUser($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->insert('users')
            ->values([  'login' => '?',
                        'password' => '?',
                        'email' => '?',
                        'nom' => '?',
                        'code_postal'=>'?',
                        'ville' => '?',
                        'adresse'=>'?'])
            ->setParameter(0, $donnees['login'])
            ->setParameter(1, $donnees['password'])
            ->setParameter(2, $donnees['email'])
            ->setParameter(3, $donnees['nom'])
            ->setParameter(4, $donnees['code_postal'])
            ->setParameter(5, $donnees['ville'])
            ->setParameter(6, $donnees['adresse']) ;

        return $queryBuilder->execute();

}}