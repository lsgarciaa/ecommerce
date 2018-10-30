<?php 

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;

class User extends Model {

	const SESSION = "User";

	public static function getFromSession(){

		$user = new User();

		if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){

			

			$user->setData($_SESSION[User::SESSION]);
		}

		return $user;
	}

	public static function checkLogin($inadmin = true)
	{

		if(!isset($_SESSION[User::SESSION])
			|| 
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0

			){
			
			
			return false;

		}else{

			if($inadmin === true && $_SESSION[User::SESSION]["inadmin"] === true){//so ira acontecer esse IF se o usuario tentar acessar uma rota de adm

				return true;

			}else if($inadmin === false){

				return true;

			}else{

				return false;
			}

		}


	}

	protected $fields = [
		"iduser", "idperson", "deslogin", "despassword", "inadmin", "dtergister"
	];

	public static function login($login, $password):User
	{

		$db = new Sql();

		$results = $db->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		if (count($results) === 0) {
			throw new \Exception("N]ao foi possivel realizar o login.");
		}

		$data = $results[0];


		if (password_verify($password, $data["despassword"])) {


			$user = new User();

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else {

			throw new \Exception("Login nao realizado.");

		}

	}

	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;

	}

	public static function verifyLogin($inadmin = true)
	{

		if (User::checkLogin($inadmin)){
			
			
			header("Location: /admin/login");
			exit;

		}

	}


	public static function listAll()

	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
	}

	public function get($iduser)
	{
 
 	$sql = new Sql();
 
	 
	 $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser;", array(

 	":iduser"=>$iduser
 	));
 
 	$data = $results[0];
 
 	$this->setData($data);
 
	 }


	 public function save(){
	 	$options = [

   		 'cost' => 12,

			];

		$senha = password_hash($this->getdespassword(), PASSWORD_BCRYPT, $options);


	 	$sql = new Sql();

	 	$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail , :nrphone, :inadmin )", array(

	 		":desperson"=>$this->getdesperson(),
	 		":deslogin"=>$this->getdeslogin(),
	 		":despassword"=>$senha,
	 		":desemail"=>$this->getdesemail(),
	 		":nrphone"=>$this->getnrphone(),
	 		":inadmin"=>$this->getinadmin()


	 	));

	 	$this->setData($results[0]);

	 }

	 public function update(){

	 	$sql = new Sql();

	 	$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail , :nrphone, :inadmin )", array(
	 		"iduser"=>$this->getiduser(),
	 		":desperson"=>$this->getdesperson(),
	 		":deslogin"=>$this->getdeslogin(),
	 		":despassword"=>$this->getdespassword(),
	 		":desemail"=>$this->getdesemail(),
	 		":nrphone"=>$this->getnrphone(),
	 		":inadmin"=>$this->getinadmin()


	 	));

	 	$this->setData($results[0]);

	 }


	 public function delete(){

	 	$sql = new Sql();

	 	$sql->query("CALL sp_users_delete(:iduser)", array(
	 		":iduser"=>$this->getiduser()				

	 	));



}

}

 ?>