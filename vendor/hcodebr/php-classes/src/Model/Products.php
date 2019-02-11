<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
USE \Hcode\Mailer;

class Product extends Model{

	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
	}

	public function save(){

        $sql = new Sql();

        $results = $sql->select("CALL sp_products_save(:idproduct, :desproducts, :vlprice, :vlwidht, :vlheight, vllength, :vlweight, :desurl)", array(
            ":idproduct"=>$this->getidproduct(),
            ":desproducts"=>$this->getdesproducts(),
            ":vlprice"=>$this->getvlprice(),
            ":vlwidht"=>$this->getvlwidht(),
            ":vlheight"=>$this->getvlheight(),
            ":vllengtht"=>$this->getvllength(),
            ":vlweight"=>$this->getvlweight(),
            ":desurl"=>$this->getdesurl(),
        ));

        $this->setData($results[0]);
    }


    public function get($idproduct){

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", array(
            ":idproduct"=>$idproduct
        ));

        $this->setData($results[0]);
    }

    public function delete(){

        $sql = new Sql();

        $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", array(
            ":idproduct"=>$this->getidproduct()
        ));
    }
}

?>