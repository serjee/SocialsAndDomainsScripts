<?php
if($error)
{
    echo '
    <div class="alert alert-block alert-error fade in" style="text-align:left;">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <h4 class="alert-heading">Ой, ошибочка вышла!</h4>
        <p>'.$error.'</p>
    </div>';
}
else
{
    if (count($data)>1)
    {
        $outputData = <<<HTML
<table class="table">
<thead>
    <tr>
        <th>#</th>
        <th>Доменное имя</th>
        <th>Статус</th>
        <th>Цена</th>
        <th>Действие</th>
    </tr>
</thead>
<tbody>
HTML;
        foreach($data as $domain)
        {
            if ($domain['isavailable'])
            {
                $domainRegLink = Yii::app()->createUrl('user/profile', $params = array('name'=>$domain['domain']));
                $outputData .= <<<HTML
    <tr class="success">
        <td>#</td>
        <td><b>{$domain['domain']}</b></td>
        <td>Свободно</td>
        <td>{$domain['price']} руб.</td>
        <td><a class="btn btn-mini btn-success" href="{$domainRegLink}"><i class="icon-hand-right icon-white"></i> Выбрать имя</a></td>
    </tr>
HTML;
            }
            else
            {
                $outputData .= <<<HTML
    <tr class="warning">
        <td>#</td>
        <td class="muted">{$domain['domain']}</td>
        <td class="muted">Занято</td>
        <td></td>
        <td><a class="btn btn-mini btn" href="#iwhois" data-toggle="modal" onclick="whoischeckdomain('{$domain['domain']}');"><i class="icon-info-sign"></i> Информация</a></td>
    </tr>
HTML;
            }
        }
        $outputData .= '</tbody></table>';
        
        echo $outputData;
    }
    else
    {
        if ($data[0]['isavailable'])
        {
            $domainRegLink = Yii::app()->createUrl('user/profile', $params = array('name'=>$data[0]['domain']));
            echo '
            <div class="alert alert-block alert-success fade in" style="text-align:left;">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <h4 class="alert-heading">Имя свободно!</h4>
                <p>Теперь оно может стать вашим за <b>'.$data[0]['price'].' руб</b>.! Для этого перейдите <a href="'.$domainRegLink.'" class="btn btn-success btn-small"><i class="icon-hand-right icon-white"></i> на следующий шаг</a></p>
            </div>';
        }
        else
        {
            echo '
            <div class="alert alert-block alert-error fade in" style="text-align:left;">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <h4 class="alert-heading">Это имя занято :(</h4>
                <p style="padding-top:5px;">Пожалуйста, попробуйте <a href="javascript:void(0);" onclick="enternewname();">подобрать другое имя</a>. Или <a href="javascript:void(0);" onclick="checkanother();">проверьте это имя в других зонах</a>.</p>
                <p>Также вы можете узнать о том, <a class="btn btn-mini btn" href="#iwhois" data-toggle="modal" onclick="whoischeckdomain(\''.$data[0]['domain'].'\');"><i class="icon-info-sign"></i> кто занял это имя?</a></p>
            </div>';
        }
    }    
}
?>