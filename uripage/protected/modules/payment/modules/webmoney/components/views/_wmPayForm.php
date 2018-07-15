<div class="pay_desc">
    <p>На следующем шаге вы перейдете на сайт платежной системы WebMoney для авторизации платежа.</p>
    <p style="font-size:16px;color: #9e0b0f;">Сумма оплаты <strong><?php echo floatval($orderSum);?></strong> рублей.</p>
</div>

<?php
	echo CHtml::beginForm('https://merchant.webmoney.ru/lmi/payment.asp'),
		 CHtml::hiddenField('LMI_PAYMENT_AMOUNT',floatval($orderSum)),
         CHtml::hiddenField('LMI_PAYMENT_DESC_BASE64', base64_encode( Yii::t('WmPayForm',$orderMessage,array('{$orderId}'=>$orderId,'{$userName}'=>Yii::app()->user->email)) )),
		 CHtml::hiddenField('LMI_PAYMENT_NO',$orderId),
		 CHtml::hiddenField('LMI_PAYEE_PURSE',$options['purse']);
	echo ($options['mode']==9)?'':CHtml::hiddenField('LMI_SIM_MODE',$options['mode']);
	echo ($csrfToken)?CHtml::hiddenField('YII_CSRF_TOKEN',Yii::app()->request->csrfToken):'';
?>

    <?php echo CHtml::submitButton('Оплатить', array('class'=>'btn btn-large btn-success')); ?>

<?php echo CHtml::endForm(); ?>