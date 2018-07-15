<!-- HeadMenu  -->
<?php
$userModel = User::model()->findByPk(Yii::app()->user->id);
$userBalance = 'Баланс '.floatval($userModel['balance']).' руб.';
$this->widget('Menu', array(
    'items' => array(
        array('label' => $userBalance, 'url'=>array('/payment'), 'visible'=>!Yii::app()->user->isGuest),
        array('label' => Yii::t('main', 'Profile'), 'url'=>array('/user/profile/settings'), 'visible'=>!Yii::app()->user->isGuest),
        array('label' => Yii::t('main', 'Panel'), 'url'=>array('/admin/'), 'visible'=>Yii::app()->user->checkAccess('ADMIN')),
        array('label' => Yii::t('main', 'Login'), 'url'=>array('/user/account'), 'visible'=>Yii::app()->user->isGuest),
        array('label' => Yii::t('main', 'Logout'), 'url'=>array('/user/account/logout'), 'visible'=>!Yii::app()->user->isGuest),
    ),
    'htmlOptions' => array('class' => 'nav nav-pills pull-right'),
    'activeCssClass' => 'active',
    'itemTemplate' => '{menu}',
));
?>
<!-- /HeadMenu -->