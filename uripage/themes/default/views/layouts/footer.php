<div class="footer">
    <p>
        <?=Yii::t('news', 'Powered by');?> &copy; <?=Yii::t('main', 'www.uripage.com');?>, <?php echo date('Y') ?><br />
        <?=Yii::t('main', 'Using the site, you agree to');?> <a href="#"><?=Yii::t('main', 'License');?></a><br />
        <!-- for debug -->
        <?=Yii::t('main', 'Generate time');?>: <?=sprintf('%0.5f',Yii::getLogger()->getExecutionTime())?>s. <?=Yii::t('main', 'Memory used');?>: <?=round(memory_get_peak_usage()/(1024*1024),2)."Mb"?><br />
        <!-- /for debug -->
    </p>
    <div class="wegetpay">Мы принимаем к оплате:</div>
    <ul class="pay_methods_list">
        <li class="item_1"><a href="#" target="_blank">WebMoney</a></li>
        <li class="item_2"><a href="#" target="_blank">Яндекс.Деньги</a></li>        
        <li class="item_3"><a href="#" target="_blank">Qiwi</a></li>
        <li class="item_4"><a href="#" target="_blank">PayPal</a></li>
    </ul>
 </div>