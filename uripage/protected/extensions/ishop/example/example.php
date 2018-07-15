<?php
class ExampleController extends CController {
    
    public function actionIndex() {
        $user = 9181234567;
        $amount = 100;
        $coment = 'Test pay';
        $txn = 123;
        $ishop = new IShop();
        $return = $ishop->createBill($user, $amount, $comment, $txn);
        echo $return;
    }
}