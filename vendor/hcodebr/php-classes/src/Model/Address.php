<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Address extends Model{

	const SESSION_ERROR = "Address Error";

	public static function getCEP($nrcep){

		$nrcep = str_replace("-", "", $nrcep);

		//https://viacep.com.br/ws/01001000/json/

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "https://viacep.com.br/ws/$nrcep/json/"); //o endereço desejado
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //algum retorno
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //alguma autenticação ssl

		$data = json_decode(curl_exec($ch), true); //pegar a resposta da curl

		curl_close($ch);

		return $data;
	}

	public function loadFromCEP($nrcep){

		$data = Address::getCEP($nrcep);

		if(isset($data['logradouro']) && $data['logradouro']){

			$this->setdesaddress($data['logradouro']);
			$this->setdescomplement($data['complemento']);
			$this->setdesdistrict($data['bairro']);
			$this->setdescity($data['localidade']);
			$this->setdesstate($data['uf']);
			$this->setdescountry('Brasil');
			$this->setdeszipcode($nrcep);
		}
	}

	public function save(){

		$sql = new Sql();

		$results = $sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :desnumber,  :descomplement, :descity, :desstate, :descountry, :deszipcode, :desdistrict)", [
			':idaddress'=>$this->getidaddress(),
			':idperson'=>$this->getidperson(),
			':desaddress'=>utf8_decode($this->getdesaddress()),
			':desnumber'=>$this->getdesnumber(),
			':descomplement'=>utf8_decode($this->getdescomplement()),
			':descity'=>utf8_decode($this->getdescity()),
			':desstate'=>utf8_decode($this->getdesstate()),
			':descountry'=>utf8_decode($this->getdescountry()),
			':deszipcode'=>utf8_decode($this->getdeszipcode()),
			':desdistrict'=>utf8_decode($this->getdesdistrict())
		]);

		if (count($results) > 0) {
			
			$this->setData($results[0]);
		}
	}

	public static function setmsgError($msg){

     	$_SESSION[Address::SESSION_ERROR] = $msg;

     }

     public static function getMsgError(){

     	$msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";

     	Address::clearMsgError();

     	return $msg;
     }

     public static function clearMsgError(){

     	$_SESSION[Address::SESSION_ERROR] = NULL;
     }
}

?>