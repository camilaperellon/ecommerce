<?php

namespace Hcode;

class Model{

	private $values = [];

	public function __call($name,$args){

		$method = substr($name, 0, 3); // o três significa QUANTIDADE e Não o INDICE do array.
		$fieldName = substr($name, 3, strlen($name));

		switch ($method) {
			case "get":
				return $this->values[$fieldName];
				break;

			case "set":
				$this->values[$fieldName] = $args[0];
				break;
		}
	}


	public function setData($data = array()){

		foreach ($data as $key => $value) {
			$this->{"set" . $key}($value);   //tudo que é dinâmico no php, tem que ser entre chaves

		}
	}

	public function getValues(){

		return $this->values;

	}
}

?>
