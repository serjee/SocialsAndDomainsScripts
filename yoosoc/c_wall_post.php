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

// Логирование
$fpLOG = @fopen($_SERVER["DOCUMENT_ROOT"]."/logs/c_wall_post.log", "a+");

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

    // Список доступных действий для аккаунта
    $arActions = array(
        //'STATUS_TO_WALL', // Постинг произвольных статусов на своей стене
        'REPOST_FROM_GROUPS' // Репост записей из подписанных групп
    );

    // Выбираем случайный тип действия и производим его
    $randKeyActions = array_rand($arActions);
    switch($arActions[$randKeyActions])
    {
        case 'STATUS_TO_WALL':
            //postStatus($vkApi, $user_id, $fpLOG); банят за это :((
            break;
        case 'REPOST_FROM_GROUPS':
            repostToWall($vkApi, $user_id);
            break;
    }

}

@fclose($fpLOG);

/**
 * Выбирает произвольный статус из базы и постит его на стену пользователя
 *
 * @param CVkRequest $vkApi
 * @param $user_id
 * @param $fpLOG
 */
function postStatus(CVkRequest $vkApi, $user_id, $fpLOG)
{
    global $DB;

    // Вытаскиваем произвольную запись из нашей базы "статусов"
    $numRND = rand(1,22858); // в базе у нас именно столько записей статусов - 22858 шт.

    $stRandRes = $DB->prepare("SELECT data FROM yoo_db_status WHERE id=:id");
    $stRandRes->execute(array(':id'=>$numRND));
    if($randStatus = $stRandRes->fetch())
    {
        // Делаем пост на свою стену
        $out = $vkApi->postToWall($user_id, $randStatus["data"]);

        // Если вернулся ответ
        if($out)
        {
            // Если VK вернул ошибку, то пишем ее в лог
            if(property_exists($out, "error"))
                @fputs($fpLOG, "[".date("Y-m-d H:i:s")."] ERROR: USER_ID[".$user_id."] - ".$out->error->error_msg.".\r\n");
        }
    }
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