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
$stAcRes = $DB->prepare("SELECT id, user_id, access_token, secret FROM yoo_accounts WHERE id='6' AND soc='VK' AND status IN('ACTIVE','UNDERWAY','SALE','GROUP')");
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
    repostToWall($vkApi, $user_id);

}

/**
 * Делаем репост последней записи из произвольной группы, в которых состоит пользователь
 *
 * @param CVkRequest $vkApi
 * @param $user_id
 */
function repostToWall(CVkRequest $vkApi, $user_id)
{
    // Получаем все IDs групп на которые подписан пользователь
    $grpItems = $vkApi->groupsGetOwner($user_id);

    // Если вернулся ответ
    if($grpItems)
    {
        // Проверяем ответ
        if(property_exists($grpItems, "response"))
        {
            if($grpItems->response->count > 0 && is_array($grpItems->response->items))
            {
                $arUserGroups = $grpItems->response->items;

                // выбираем произвольную группу
                $randKeyUserGroups = array_rand($arUserGroups);

                // выбираем на стене группы последнюю запись
                $lastPost = $vkApi->wallGet(-$arUserGroups[$randKeyUserGroups], "all");

                // Если вернулся ответ
                if($lastPost)
                {
                    // Если VK вернул ответ
                    if(property_exists($lastPost, "response"))
                    {
                        // Если на стене пользователя есть запись для лайка
                        if(count($lastPost->response->items)>0)
                        {
                            // Формируем идентификатор для объекта записи и делаем репост
                            $objectWall = "wall-".$arUserGroups[$randKeyUserGroups]."_".$lastPost->response->items[0]->id;
                            $vkApi->wallRepost($objectWall);
                        }
                    }
                }
            }
        }
    }
}