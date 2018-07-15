<div class="container-fluid">
    <h2><?=Yii::t('UserModule.user','Profile'); ?></h2>

    <?php if(Yii::app()->user->hasFlash('editMessage')): ?>
        <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <?php echo Yii::app()->user->getFlash('editMessage'); ?>
        </div>
    <?php endif; ?>

    <div class="row-fluid">
        <div class="span4">
            <div class="well pull-left" style="min-width: 200px; margin: 5px; padding: 8px 0;">
            <ul class="nav nav-list">
                <li class="nav-header">Профиль</li>
                <li><a href="<?php echo Yii::app()->createUrl('/user/profile'); ?>"><i class="icon-user"></i> Мои профили</a></li>
                <li><a href="<?php echo Yii::app()->createUrl('/user/profile/settings'); ?>"><i class="icon-cog"></i> Настройки</a></li>
                <li class="nav-header">Платежи</li>
                <li><a href="<?php echo Yii::app()->createUrl('/payment'); ?>"><i class="icon-plus"></i> Пополнить баланс</a></li>
                <li><a href="<?php echo Yii::app()->createUrl('/user/profile/orders/'); ?>"><i class="icon-certificate"></i> Мои заказы</a></li>
                <li class="nav-header">Домены</li>
                <li><a href="#"><i class="icon-plus"></i> Зарегистрировать имя</a></li>
                <li><a href="#"><i class="icon-eye-open"></i> Мои доменные имена</a></li>
                <li class="divider"></li>
                <li><a href="#"><i class="icon-flag"></i> Помощь</a></li>
            </ul>
            </div>
        </div>
        <div class="span8">

            <h4>Настройки</h4>

            <?php $form=$this->beginWidget('CActiveForm', array(
                'id'=>'changepwd-form',
                'htmlOptions'=>array(
                    'class'=>'form-settings',
                ),
            )); ?>

            <div class="alert alert-block">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?php echo Yii::t('UserModule.user', 'Required fields');?>
            </div>

            <div class="control-group">
                <?php echo $form->labelEx($model2,'oldPassword',array('class'=>'control-label')); ?>
                <?php echo $form->passwordField($model2,'oldPassword'); ?>
                <span class="help-inline"><?php echo $form->error($model2,'oldPassword'); ?></span>
            </div>

            <div class="control-group">
                <?php echo $form->labelEx($model2,'password',array('class'=>'control-label')); ?>
                <?php echo $form->passwordField($model2,'password'); ?>
                <span class="help-inline"><?php echo $form->error($model2,'password'); ?></span>
            </div>

            <div class="control-group">
                <?php echo $form->labelEx($model2,'verifyPassword',array('class'=>'control-label')); ?>
                <?php echo $form->passwordField($model2,'verifyPassword'); ?>
                <span class="help-inline"><?php echo $form->error($model2,'verifyPassword'); ?></span>
            </div>

            <div class="control-group">
                <?php echo CHtml::submitButton(Yii::t('UserModule.user', 'Save'),array('class'=>'btn btn-large btn-success')); ?>
            </div>

            <?php $this->endWidget(); ?>
            <!-- form -->

        </div>
    </div>
</div>