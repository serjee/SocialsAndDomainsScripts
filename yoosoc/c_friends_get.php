<?php
/**
 * Скрипт обновляет статистику по количеству друзей у пользователей.
 * Является частью системы "Статистики".
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

// Счетчик попыток для запроса
$repeatCnt = 0;

// Вытаскиваем аккаут из базы, под которым будем делать запросы к API
$rowAcc = $DB->prepare("SELECT id, user_id, access_token, secret FROM yoo_accounts WHERE id=:id");
$rowAcc->execute(array(':id'=>23)); // Берем только первый аккаунт
if($rowAcc = $rowAcc->fetch())
{
    // Вытаскиваем все аккаунты для которых нужно обновить статистику в массив
    $arStatAccounts = array();
    foreach ($DB->query("SELECT id, user_id FROM yoo_accounts WHERE soc='VK' AND status IN ('ACTIVE','UNDERWAY','SALE','TEST','GROUP')") as $rowStatAcc)
    {
        $arStatAccounts[$rowStatAcc['id']] = $rowStatAcc['user_id'];
    }

    // Если массив с аккаунтами не пустой, обрабатываем его
    if (count($arStatAccounts)>0)
    {
        // Создаем объект класса для пользователя делающего запрос к API
        $vkApi = new CVkRequest($DB, $rowAcc["id"], $rowAcc['access_token'], $rowAcc['secret'], true);

        // Перебираем пользователей по одному (требование вконтакте запрашивать counters индивидуально)
        foreach($arStatAccounts as $stAccKey=>$stAccVal)
        {
            $stUserResponse = $vkApi->usersGet($stAccVal);
            //var_dump($stUserResponse);
            if(property_exists($stUserResponse, "response"))
            {
                if(count($stUserResponse->response[0])>0)
                {
                    // Обновляем статистику в базе аккаунтов
                    $stmt = $DB->prepare("UPDATE yoo_accounts SET count_friends=:count_friends, count_followers=:count_followers WHERE id=:id");
                    $stmt->bindParam(':count_friends', $count_friends);
                    $stmt->bindParam(':count_followers', $count_followers);
                    $stmt->bindParam(':id', $uID);
                    $count_friends = intval($stUserResponse->response[0]->counters->friends);
                    $count_followers = intval($stUserResponse->response[0]->counters->followers);
                    $uID = $stAccKey;
                    $stmt->execute();
                }
            }
        }
    }
}