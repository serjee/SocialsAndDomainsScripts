<?php
$cs = Yii::app()->getClientScript();
$cs->registerScript("tooltip","
$('[rel=tooltip]').tooltip();
",CClientScript::POS_READY);
?>

<div class="jumbotron">
    <?php $this->renderPartial('../../main/_header_top');?>
    <?php $this->renderPartial('../../main/_header_steps');?>
</div>

<hr />

<?php if(Yii::app()->user->hasFlash('error')) { ?>
    <div class="alert alert-error">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <?php echo Yii::app()->user->getFlash('error'); ?>
    </div>
<?php } else { ?>

<div class="row-fluid">

    <div class="span12">
        <div class="pay_head">Выбор способа <?php echo ($orderId)?'оплаты':'пополнения баланса';?></div>
        <div class="pay_desc">
            <p>Вы можете моментально <?php echo ($orderId)?'оплатить выбранное имя':'пополнить баланс';?> с помощью электронных платёжных систем (WebMoney, Яндекс.Деньги, QIWI Кошелек и др.) либо с Вашего личного счета на сайте или PIN-кода.</p>
        </div>
        <div class="pay_btn">
            <?php $form=$this->beginWidget('CActiveForm', array(
                'id'=>'choosePayment',
                'htmlOptions'=>array('class'=>'form-inline'),
            ));

                echo $form->hiddenField($model,'paysys');

                if ($orderId)
                {
                    echo '<p class="price-input">Оплата на сумму ' . $form->textField($model,'amount',array('class'=>'span1','value'=>$orderSum,'disabled'=>'disabled')) . ' рублей.</p>';
                    echo $form->hiddenField($model,'order_id',array('value'=>$orderId));
                    if ($form->error($model,'order_id')) echo '<div class="alert alert-error">'.$form->error($model,'order_id').'</div>';
                }
                else
                {
                    echo '<p class="price-input">Пополнение баланса на сумму ' . $form->textField($model,'amount',array('class'=>'span1','value'=>'100')) . ' рублей.</p>';
                    if ($form->error($model,'amount')) echo '<div class="alert alert-error">'.$form->error($model,'amount').'</div>';
                }
                if ($form->error($model,'paysys')) echo '<div class="alert alert-error">'.$form->error($model,'paysys').'</div>';
            ?>
                <p class="price-desc">Выберите наиболее подходящий для вас способ оплаты, кликнув на соответствующую кнопку:</p>
                <div class="btn-group">
                    <button class="btn btn-large" type="button" onclick="document.getElementById('OrderForm_paysys').value='WMR'; document.getElementById('choosePayment').submit();"><?php echo CHtml::image(Yii::app()->theme->baseUrl.'/views/payment/web/img/wm.png', 'Оплата через WebMoney'); ?></button>
                    <button class="btn btn-large" type="button" onclick="document.getElementById('OrderForm_paysys').value='QIWI'; document.getElementById('choosePayment').submit();"><?php echo CHtml::image(Yii::app()->theme->baseUrl.'/views/payment/web/img/qiwi.png', 'Оплата через Qiwi-кошелек'); ?></button>
                    <button class="btn btn-large" type="button" onclick="document.getElementById('OrderForm_paysys').value='YAM'; document.getElementById('choosePayment').submit();"><?php echo CHtml::image(Yii::app()->theme->baseUrl.'/views/payment/web/img/yam.png', 'Оплата через Яндекс.Деньги'); ?></button>
                    <button class="btn btn-large" type="button" onclick="document.getElementById('OrderForm_paysys').value='PAYPAL'; document.getElementById('choosePayment').submit();"><?php echo CHtml::image(Yii::app()->theme->baseUrl.'/views/payment/web/img/paypal.png', 'Оплата через PayPal'); ?></button>
                    <button class="btn btn-large" type="button" onclick="document.getElementById('OrderForm_paysys').value='LC'; document.getElementById('choosePayment').submit();"><?php echo CHtml::image(Yii::app()->theme->baseUrl.'/views/payment/web/img/wallet.png', 'Оплата с Вашего личного счета'); ?></button>
                </div>
            <?php $this->endWidget(); ?>
        </div>
        <div class="pay_pin">
            <form>
                <fieldset>
                    <legend><?php echo ($orderId)?'Оплата':'Пополнение баланса';?> при помощи купона</legend>
                    <label>Введите код купона</label>
                    <input class="input-xlarge" type="text" placeholder="PIN-123">
                    <span class="help-block">Информацию о том, как получить купон для оплаты, Вы можете <a href="#">узнать здесь</a>.</span>
                    <button type="submit" class="btn btn-large btn-success">Активировать купон</button>
                </fieldset>
            </form>
        </div>
        <div class="well well-small">
            <ul class="nav nav-pills">
                <li><a href="javascript:void(0)" rel="tooltip" data-placement="top" data-original-title="Через несколько минут после оплаты, денежные средства будут зачислены на ваш личный счет на сайте. Если вы уже выбрали имя и оформили заказ, то оплата произойдет автоматически. Время регистрации доменного имени может занимать до 12 часов.">Что будет<br>после оплаты?</a></li>
                <li><a href="javascript:void(0)" rel="tooltip" data-placement="top" data-original-title="Остаток денежных средств после оплаты доменного имени поступит на ваш личный счет на сайте. Вы сможете воспользоваться этими денежными средствами для покупки любого понравившегося имени или дополнительной услуги.">Что, если я внесу больше,<br>чем стоимость имени?</a></li>
                <li><a href="javascript:void(0)" rel="tooltip" data-placement="top" data-original-title="Безусловно. Все операции на сайте защищены современными системами безопасности. Мы не храним данные ваших платежных систем.">Вы гарантируете<br>конфиденциальность?</a></li>
                <li><a href="javascript:void(0)" rel="tooltip" data-placement="top" data-original-title="Вы можете отказаться от покупки доменного имени только до его оплаты. Когда оплата уже произведена, выбранное вами доменное имя автоматически будет зарегистрировано на вас и отменить это действие уже нельзя.">Смогу ли я отказаться<br>от покупки?</a></li>
            </ul>
        </div>
    </div>
</div>

<?php } ?>