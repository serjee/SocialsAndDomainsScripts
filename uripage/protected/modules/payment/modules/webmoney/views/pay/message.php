<?php if(Yii::app()->user->hasFlash('error')) : ?>
    <div class="alert alert-error">
        <strong>Ошибка!</strong>
        <p><?php echo Yii::app()->user->getFlash('error'); ?></p>
    </div>
<?php endif; ?>

<?php if(Yii::app()->user->hasFlash('success')) : ?>
    <div class="alert alert-success">
        <strong>Поздравляем!</strong>
        <p><?php echo Yii::app()->user->getFlash('success'); ?></p>
    </div>
<?php endif; ?>