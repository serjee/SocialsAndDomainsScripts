<?php
$cs = Yii::app()->getClientScript();
$cs->registerScriptFile(Yii::app()->theme->baseUrl.'/web/js/whois.js',CClientScript::POS_HEAD);
$cs->registerScript("whoischeck","
function whoischeckdomain(domainname) {".
CHtml::ajax(array( // ajaxOptions
    'type' => 'post',
    'cache' => false,
    'url' => Yii::app()->createUrl('whois/ajax/WhoisInfo'),
    'data' => array('domain'=>'js:domainname',Yii::app()->request->csrfTokenName => Yii::app()->request->csrfToken ),
    'beforeSend' => "js:function(){        
        $('#iwhois .modal-body').addClass('loading');
    }",
    'success' => "js:function(data){
        $('#iwhois .modal-body').removeClass('loading');
        $('#iwhois .modal-body').empty();
        $('#iwhois .modal-body').html(data);
        $('#iwhois').modal({keyboard:false});
    }",
))."}",CClientScript::POS_HEAD);
?>
<div class="jumbotron">
    <?php $this->renderPartial('_header_top');?>
    <?php $this->renderPartial('_header_steps');?>    
    <div class="input-append">
    <?php echo CHtml::form();?>
        <?php echo CHtml::textField('whoisinput', $whoisinput, array('class'=>'span4 big','id'=>'whoisfield','placeholder'=>'пример: web')); ?>
        <?php echo CHtml::ajaxButton (
            'Проверить',
            CHtml::normalizeUrl(array('whois/ajax/DomainCheck')), 
            array(
                'type'=>'post',
                'update'=>'#whoisdata',
                'beforeSend' => 'function(){
                    $("#whoisdata").empty();
                    $("#whoisdata").addClass("loading");
                 }',
                'complete' => 'function(){
                    $("#whoisdata").removeClass("loading");
                }',
            ),
            array(
                'id'=>'whoisbtn',
                'class'=>'btn big',
                'type'=>'submit',
            )
        );?>
    <?php echo CHtml::endForm();?>
    </div>
    <div id="whoisdata"></div>
</div>
<hr />
<div class="row-fluid">
    <div class="span6 mainpage">
        <?php echo CHtml::image(Yii::app()->theme->baseUrl.'/web/img/name.png', 'Получить свое имя в интернете'); ?>
        <p>Получи свое имя в интернете, которое легко запоминается и по которому ваши друзья теперь смогут быстро найти вас !</p>

        <ul class="media-list">
            <li class="media">
                <a class="pull-left" href="#">
                    <?php echo CHtml::image(Yii::app()->theme->baseUrl.'/web/img/webpage.png', 'Создать свою страничку или сайт'); ?>
                </a>
                <div class="media-body">
                    <h4 class="media-heading">Создай страничку или сайт</h4>
                    Чтобы разместить информацию о себе и ссылки на профили в социальных сетях !
                </div>
            </li>
        </ul>
    </div>
    <div class="span6 mainpage">
        <?php echo CHtml::image(Yii::app()->theme->baseUrl.'/web/img/icons.png', 'Короткий адрес для профиля в социальных сетях'); ?>
        <p>Привяжи профиль в соц. сетях и войти на вашу страничку можно будет, набрав в браузере "<strong>yourname.ru</strong>" или "<strong>вашеимя.рф</strong>" !</p>

        <ul class="media-list">
            <li class="media">
                <a class="pull-left" href="#">
                    <?php echo CHtml::image(Yii::app()->theme->baseUrl.'/web/img/surprize.png', 'Создать свою страничку или сайт'); ?>
                </a>
                <div class="media-body">
                    <h4 class="media-heading">Сделай подарок друзьям</h4>
                    Выберите и подарите Имя в интернете человеку с именным сертификатом !
                </div>
            </li>
        </ul>
    </div>
</div>

<!-- Modal -->
<div id="iwhois" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Информация о владельце домена</h3>
  </div>
  <div class="modal-body"></div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Закрыть</button>
  </div>
</div>