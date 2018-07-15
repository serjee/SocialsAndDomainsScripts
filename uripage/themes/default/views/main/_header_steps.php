<div id="stNav">
    <ul id="stepNavigator">
    <?php if (Yii::app()->getController()->getId()==='main'):?>
   	    <li class="current"><b class="cu">Шаг 1</b><span>Выбрать имя</span></li>
    <?php else:?>
        <li class="active">
            <div class="on"><a href="/"><b class="cu">Шаг 1</b><span>Выбрать имя</span></a></div>
        </li>
    <?php endif;?>
    <?php if (Yii::app()->getController()->getId()==='profile'):?>
        <li class="current"><b class="cu">Шаг 2</b><span>Заполнить профиль</span></li>
    <?php elseif(Yii::app()->controller->module->id==='payment'):?>
        <li class="active">
            <div class="on"><a href="<?php echo Yii::app()->createUrl('/user/profile/');?>"><b class="cu">Шаг 2</b><span>Заполнить профиль</span></a></div>
        </li>
    <?php else:?>
        <li class="passive">
        	<div class="off"><b class="cu">Шаг 2</b><span>Заполнить профиль</span></div>
        </li>
    <?php endif;?>
    <?php if (Yii::app()->controller->module->id==='payment'):?>
        <li class="current"><b class="cu">Шаг 3</b><span>Произвести оплату</span></li>
    <?php else:?>
        <li class="passive">
        	<div class="off"><b class="cu">Шаг 3</b><span>Произвести оплату</span></div>
        </li>
    <?php endif;?>
    </ul>
</div>
<?php //echo Yii::app()->getController()->getId();?>