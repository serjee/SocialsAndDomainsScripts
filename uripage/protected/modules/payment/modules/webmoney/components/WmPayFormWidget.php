<?php

class WmPayFormWidget extends CWidget
{
    public $orderSum;
    public $orderId;
    public $orderMessage=false;
    public $csrfToken=false;

    public function run()
    {
        // Вытаскиваем из базы опции для оплаты по WMR
        $options = Payoptions::model()->find(array(
            'select'=>'secret,purse,mode',
            'condition'=>'system=:system',
            'params'=>array(':system'=>'WMR'),
        ));

		$this->render('_wmPayForm',array(
			'options'=>$options,
			'orderId'=>$this->orderId,
			'orderSum'=>$this->orderSum,
			'orderMessage'=>$this->orderMessage,
			'csrfToken'=>$this->csrfToken,
		));
    }
}