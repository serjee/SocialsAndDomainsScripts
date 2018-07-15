<div class="row-fluid">
    <div class="span12">
        <div class="pay_head">Оплата через платежную систему WebMoney</div>

<?php if(Yii::app()->user->hasFlash('error')) { ?>
    <div class="alert alert-error">
        <?php echo Yii::app()->user->getFlash('error'); ?>
    </div>
<?php } else { ?>
<?php
	$this->widget('WmPayFormWidget', array(
        'orderSum'=>$order->sum,
        'orderId'=>$order->id,
		'orderMessage'=>'Оплата от пользователя {$userName} по счету № {$orderId}',
		'csrfToken'=>true,	));
?>
<?php } ?>
    </div>
</div>