<?php
/**
 * Скрипт постит на стенах пользователей из базы произвольный текст (статус);
 * Является частью системы - "Видимость активности пользователя".
 * (вызывается по CRON в определенные промежутки времени)
 */

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
@ini_set('memory_limit', '16M');
@set_time_limit(0);
@ini_set('max_execution_time',0);
@ini_set('set_time_limit',0);

header('Content-Type: text/html; charset=utf-8');
@ob_end_flush();

// Подключаем необходимые файлы
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/ext_www/sc.bount.ru";
require_once($_SERVER["DOCUMENT_ROOT"] . "/params.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/dbconn.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/class/CVkRequest.php");

// Вытаскиваем все аккаунты из базы и производим нужные действия
$arAccounts = array();
$stAcRes = $DB->prepare("SELECT id, user_id, access_token, secret FROM yoo_accounts WHERE soc='VK' AND status IN('ACTIVE','UNDERWAY','SALE','GROUP')");
$stAcRes->execute();
while ($rowAcc = $stAcRes->fetch())
{
    $arAccounts[$rowAcc['user_id']] = array('access_token'=>$rowAcc['access_token'], 'secret'=>$rowAcc['secret'], 'id'=>$rowAcc['id']);
}

// Для каждого пользователя инициализируем класс и выполняем нужные действия
foreach ($arAccounts as $user_id=>$access)
{
    // Создаем объект класса для пользователя, под которым будем производить действия
    $vkApi = new CVkRequest($DB, $access['id'], $access['access_token'], $access['secret'], true);

    // выбираем на стене группы последнюю запись
    $lastPost = $vkApi->wallGet($user_id, "others");
    if($lastPost)
    {
        if(property_exists($lastPost, "response"))
        {
            if(count($lastPost->response->items)>0)
            {
                // Проходимся по всем чужим записям и удаляем их
                foreach($lastPost->response->items as $bad_post)
                {
                    $delPost = $vkApi->userWallDelete($user_id, $bad_post->id);
                    //var_dump($bad_post->id);
                }
            }
        }
    }
}