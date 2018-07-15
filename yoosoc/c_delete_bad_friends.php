<?php
/**
 * Скрипт удаляет пользователей, которые вышли из друзей аккаунтов.
 * Является частью системы "Раскрутки аккаунтов".
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
require_once($_SERVER["DOCUMENT_ROOT"] . "/lib/functions.php");

// Вытаскиваем все аккаунты из базы и производим нужные действия
$arAccounts = array();
foreach ($DB->query("SELECT id, user_id, sex, access_token, secret FROM yoo_accounts WHERE soc='VK' AND status='UNDERWAY'") as $rowAcc)
{
    $arAccounts[$rowAcc['user_id']] = array('access_token'=>$rowAcc['access_token'], 'secret'=>$rowAcc['secret'], 'sex'=>$rowAcc['sex'], 'id'=>$rowAcc['id']);
}

// Для каждого пользователя инициализируем класс и выполняем нужные действия
foreach ($arAccounts as $user_id=>$access)
{
    // Создаем объект класса для пользователя
    $vkApi = new CVkRequest($DB, $access["id"], $access['access_token'], $access['secret'], true);

    // Вытаскиваем подписчиков пользователя
    $followersUser = $vkApi->userGetFriendRequests();

    if(property_exists($followersUser, "response"))
    {
        if(count($followersUser->response->items)>0)
        {
            // Проходимся по всем ID фолловеров и добавляем их в друзья
            foreach($followersUser->response->items as $bad_user)
            {
                if($bad_user>0)
                {
                    $deleteBadFriend = $vkApi->userFriendsDelete($bad_user);
                    sleep(2); // задержка 2 секунды перед каждым удалением, иначе будет ошибка
                }
            }
        }
    }
}
