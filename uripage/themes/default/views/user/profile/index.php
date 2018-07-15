<?php
$cs = Yii::app()->getClientScript();
$cs->registerScript("calendar","
$('#birthData').datepicker();
$('#pasportDate').datepicker();
",CClientScript::POS_READY);
?>
<div class="jumbotron">
    <?php $this->renderPartial('../../main/_header_top');?>
    <?php $this->renderPartial('../../main/_header_steps');?>
</div>
<hr />

<?php if ($error):?>

<div class="alert alert-block alert-error fade in" style="text-align:left;">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <h4 class="alert-heading">Возникла ошибка :(</h4>
    <p style="padding-top:5px;"><?php echo $error; //echo Yii::t('UserModule.user', 'Required fields');?></p>        
</div>

<?php else:?>

<div class="row-fluid">
    <div class="span12">
    
    <?php $form=$this->beginWidget('CActiveForm', array(
    	'id'=>'profile-form',
    	'enableAjaxValidation'=>true,
        'clientOptions'=>array(
    		'validateOnSubmit'=>true,
    	),
        'htmlOptions'=>array(
            'class'=>'form-horizontal',
        ),
    )); ?>
    
    <div class="alert alert-info">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <p><strong>Поздравляем Вас!</strong></p>
        <p>Вы успешно подобрали свободное имя <b>'<?php echo $domain;?>'</b>.</p>
        <p>Теперь, чтобы оно стало Вашим, его необходимо зарегистрировать. Для этого необходимо заполнить все обязательные поля формы, отмеченные звездочкой (<span class="required">*</span>).</p>
        <p>Пожалуйста, указывайте достоверные данные, они будут подтверждать Ваше право владения выбранным доменным именем.</p>
        <?php //echo Yii::t('UserModule.user', 'Required fields');?>        
    </div>
    
    <div class="profile-nav">Выбранное имя</div>
    
    <div class="control-group">
        <label class="control-label" for="inputDomain">Имя <span class="required">*</span></label>
        <div class="controls">
            <?php echo $form->textField($model,'domain',array('value'=>$domain,'disabled'=>'disabled','style'=>'color:#003767;')); ?>
        </div>
    </div>
    
    <div class="profile-nav">Ваши контакты</div>
    
    <div class="control-group">
		<?php echo $form->labelEx($model,'email',array('class'=>'control-label')); ?>
        <div class="controls">
            <?php echo $form->textField($model,'email',array('placeholder'=>'Пример: info@uripage.com')); ?>
            <span class="help-inline"><?php echo $form->error($model,'email'); ?></span>
        </div>
	</div>
    
    <div class="control-group">
		<?php echo $form->labelEx($model,'phone',array('class'=>'control-label')); ?>
        <div class="controls">
            <?php echo $form->textField($model,'phone',array('placeholder'=>'Пример: +7 495 1234567')); ?>
            <span class="help-inline"><?php echo $form->error($model,'phone'); ?></span>
        </div>
	</div>
    
    <div class="profile-nav">Личные данные</div>
    
    <?php if($profile === 'FZRU'): ?>
    
    <div class="muted small">Поля заполняются по-русски, для нерезидентов допускается заполнение на английском языке.<br>Фамилия, имя, отчество в соответствии с паспортными данными.</div>
    
    <div class="control-group">
		<?php echo $form->labelEx($model,'ru_last_name',array('class'=>'control-label')); ?>
        <div class="controls">
            <?php echo $form->textField($model,'ru_last_name',array('placeholder'=>'Пример: Иванов')); ?>
            <span class="help-inline"><?php echo $form->error($model,'ru_last_name'); ?></span>            
        </div>
	</div>
    
    <div class="control-group">
		<?php echo $form->labelEx($model,'ru_first_name',array('class'=>'control-label')); ?>
        <div class="controls">
            <?php echo $form->textField($model,'ru_first_name',array('placeholder'=>'Пример: Иван')); ?>
            <span class="help-inline"><?php echo $form->error($model,'ru_first_name'); ?></span>            
        </div>
	</div>
    
    <div class="control-group">
		<?php echo $form->labelEx($model,'ru_middle_name',array('class'=>'control-label')); ?>
        <div class="controls">
    		<?php echo $form->textField($model,'ru_middle_name',array('placeholder'=>'Пример: Иванович')); ?>
    		<span class="help-inline"><?php echo $form->error($model,'ru_middle_name'); ?></span>
        </div>
	</div>
    
    <div class="control-group">
        <?php echo $form->labelEx($model,'birth_date',array('class'=>'control-label')); ?>
        <div class="controls">
            <div class="input-append date" id="birthData" data-date="01-01-1999" data-date-format="dd-mm-yyyy" data-date-viewmode="years">
                <?php echo $form->textField($model,'birth_date',array('class'=>'span2','size'=>'16','value'=>'01-01-1999')); ?>
                <span class="add-on" style="height:30px;"><i style="margin-top:8px;" class="icon-calendar"></i></span>
            </div>
            <span class="help-inline"><?php echo $form->error($model,'birth_date'); ?></span>
        </div>
    </div>
    
    <div class="profile-nav">Паспортные данные</div>
    
    <div class="muted small">
    Обязательные поля для регистрации доменных имен в зонах .RU/.РФ. Подтверждают ваше право владение именем!<br>
    Если Вы по какой-то причине не хотите указывать эти данные, выберите имя в другой зоне, например, в .COM/.NET/.ORG<br>
    Поля заполняются по-русски. Паспорта СССР (старого образца) не принимаются!       
    </div>
    
    <div class="control-group">
		<?php echo $form->labelEx($model,'pasport_num',array('class'=>'control-label')); ?>
        <div class="controls">
    		<?php echo $form->textField($model,'pasport_num',array('placeholder'=>'Пример: 12 34 567890')); ?>
    		<span class="help-inline"><?php echo $form->error($model,'pasport_num'); ?></span>
        </div>
	</div>
    
    <div class="control-group">
		<?php echo $form->labelEx($model,'pasport_iss',array('class'=>'control-label')); ?>
        <div class="controls">
    		<?php echo $form->textField($model,'pasport_iss',array('placeholder'=>'Пример: 123 отделением полиции г. Москвы')); ?>
    		<span class="help-inline"><?php echo $form->error($model,'pasport_iss'); ?></span>
        </div>
	</div>
    
    <div class="control-group">
        <?php echo $form->labelEx($model,'pasport_date',array('class'=>'control-label')); ?>
        <div class="controls">
            <div class="input-append date" id="pasportDate" data-date="01-01-2005" data-date-format="dd-mm-yyyy" data-date-viewmode="years">
                <?php echo $form->textField($model,'pasport_date',array('class'=>'span2','size'=>'16','value'=>'01-01-2005')); ?>
                <span class="add-on" style="height:30px;"><i style="margin-top:8px;" class="icon-calendar"></i></span>
            </div>
            <span class="help-inline"><?php echo $form->error($model,'pasport_date'); ?></span>
        </div>
    </div>
    
    <div class="control-group">
		<?php echo $form->labelEx($model,'country',array('class'=>'control-label')); ?>
        <div class="controls">            
            <?php echo $form->dropDownList($model,'country',Chtml::listData($countries,'iso','country'),array('options' => array('RU'=>array('selected'=>true)))); ?>            
    		<span class="help-inline"><?php echo $form->error($model,'country'); ?></span>
        </div>
	</div>
    
    <?php endif; ?>
    
    <?php if($profile === 'INTR'): ?>
    
    <div class="control-group">
		<?php echo $form->labelEx($model,'en_first_name',array('class'=>'control-label')); ?>
        <div class="controls">
    		<?php echo $form->textField($model,'en_first_name',array('placeholder'=>'Пример: Ivan')); ?>
    		<span class="help-inline"><?php echo $form->error($model,'en_first_name'); ?></span>
        </div>
	</div>

	<div class="control-group">
		<?php echo $form->labelEx($model,'en_last_name',array('class'=>'control-label')); ?>
        <div class="controls">
    		<?php echo $form->textField($model,'en_last_name',array('placeholder'=>'Пример: Ivanov')); ?>
    		<span class="help-inline"><?php echo $form->error($model,'en_last_name'); ?></span>
        </div>
	</div>
    
    <div class="control-group">
		<?php echo $form->labelEx($model,'org_name',array('class'=>'control-label')); ?>
        <div class="controls">
    		<?php echo $form->textField($model,'org_name',array('placeholder'=>'Пример: REG.RU Ltd или Private Person для физ.лиц')); ?>
    		<span class="help-inline"><?php echo $form->error($model,'org_name'); ?></span>
        </div>
	</div>
    
    <?php endif; ?>
    
    <div class="profile-nav">Почтовый адрес</div>
    
    <div class="muted small">
    Поля заполняются по-русски, для нерезидентов допускается заполнение на английском языке.<br>
    Будет использоваться для рассылки возможных уведомлений и официальных документов.<br>
    Если у вас нет номера квартиры, тогда после номера дома Вы должны указать «частный дом».
    </div>

    <?php if($profile === 'INTR'): ?>

    <div class="control-group">
        <?php echo $form->labelEx($model,'country',array('class'=>'control-label')); ?>
        <div class="controls">
            <?php echo $form->dropDownList($model,'country',Chtml::listData($countries,'iso','country'),array('options' => array('RU'=>array('selected'=>true)))); ?>
            <span class="help-inline"><?php echo $form->error($model,'country'); ?></span>
        </div>
    </div>

    <?php endif; ?>
    
    <div class="control-group">
		<?php echo $form->labelEx($model,'pochta_code',array('class'=>'control-label')); ?>
        <div class="controls">
    		<?php echo $form->textField($model,'pochta_code',array('placeholder'=>'Пример: 123456')); ?>
    		<span class="help-inline"><?php echo $form->error($model,'pochta_code'); ?></span>
        </div>
	</div>
    
    <div class="control-group">
		<?php echo $form->labelEx($model,'pochta_region',array('class'=>'control-label')); ?>
        <div class="controls">
    		<?php echo $form->textField($model,'pochta_region',array('placeholder'=>'Пример: Московская область')); ?>
    		<span class="help-inline"><?php echo $form->error($model,'pochta_region'); ?></span>
        </div>
	</div>
    
    <div class="control-group">
		<?php echo $form->labelEx($model,'pochta_city',array('class'=>'control-label')); ?>
        <div class="controls">
    		<?php echo $form->textField($model,'pochta_city',array('placeholder'=>'Пример: г. Москва')); ?>
    		<span class="help-inline"><?php echo $form->error($model,'pochta_city'); ?></span>
        </div>
	</div>

	<div class="control-group">
		<?php echo $form->labelEx($model,'pochta_address',array('class'=>'control-label')); ?>
        <div class="controls">
            <?php echo $form->textField($model,'pochta_address',array('placeholder'=>'Пример: ул. Кошкина, д. 15, кв. 4')); ?>
            <span class="help-inline"><?php echo $form->error($model,'pochta_address'); ?></span>
        </div>
	</div>

    <?php if($profile === 'FZRU'): ?>

    <div class="control-group">
		<?php echo $form->labelEx($model,'pochta_to',array('class'=>'control-label')); ?>
        <div class="controls">
    		<?php echo $form->textField($model,'pochta_to',array('placeholder'=>'Пример: Иванов Иван Иванович')); ?>
    		<span class="help-inline"><?php echo $form->error($model,'pochta_to'); ?></span>
        </div>
	</div>

    <?php endif; ?>
    
    <div class="control-group center">
		<?php echo CHtml::submitButton('Следующий шаг',array('class'=>'btn btn-large btn-success')); ?>
	</div>
    
    <?php $this->endWidget(); ?>
    
    </div>
</div>

<?php endif;?>