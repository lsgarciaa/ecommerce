<?php 

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Carrinho extends Model {

	const SESSION = "Carrinho";//criar a constante para fazer a verificação se o usuario esta em uma sessao dentro do site

	public static function getFromSession()
	{
		$carrinho = new Carrinho();

		if(isset($_SESSION[Carrinho::SESSION]) && (int)$_SESSION[Carrinho::SESSION]['idcart']> 0 ){ //verificação da constante para ver se esta dentro da sessao, ver se é maior do que 0

			$carrinho->get((int)$_SESSION[Carrinho::SESSION]['idcart']);

		}else{

			$carrinho->getFromSessionID();// Carregar o carrinho

			if (!(int)$carrinho->getidcart()>0){

				$data=[
					'dessessionid'=>session_id()//é como se fosse o endereço unico da sessao

				];

				if(User::checkLogin(false)){

					$user = User::getFromSession();

					$data['iduser'] = $user->getiduser();

				}

				$carrinho->setData($data);

				$carrinho->save();

				$carrinho->setToSession();


			}

		}

		return $carrinho;
	}

	public function setToSession(){

		$_SESSION[Carrinho::SESSION]= $this->getValues();


	}

	public function getFromSessionID(){//Toda vez que o usuario entrar pela primeira vez no carrinho, ele iniciara uma sessao com o ID que esta na sessao.

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid",[
			'dessessionid'=>session_id()//funcao que pega o valor direto pelo PHP
		]);

		if(count($results) > 0){

		$this->setData($results[0]);

		}
	}

	public function get(int $idcart){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart",[
			'idcart'=>$idcart
		]);

		if(count($results) > 0){//necessita ser maior do que 0

		$this->setData($results[0]);

		}

		}

	public function save()//função criada que chama a sp da inserção dentro do carrinho, para salvar os produtos
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)",[
			':idcart'=>$this->getidcart(),
			':dessessionid'=>$this->getdessessionid(),
			':iduser'=>$this->getiduser(),
			':deszipcode'=>$this->getdeszipcode(),
			':vlfreight'=>$this->getvlfreight(),
			':nrdays'=>$this->getnrdays()
		]);

		$this->setData($results[0]);
	}

}

 ?>