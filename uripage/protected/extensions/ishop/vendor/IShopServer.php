<?php
/**
 * @package IShopServer
 */
class Response {
  public $updateBillResult;
}

class Param {
  public $login;
  public $password;
  public $txn;      
  public $status;
}

class IShopServer {
    
    public function updateBill($status) {
        $responce = new Response();
		$responce->updateBillResult = $status;
		return $responce;
    }
}