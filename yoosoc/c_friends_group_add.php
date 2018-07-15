<?php
/**
 * Скрипт постит в спец.группы просьбы о добавлении пользователя в друзья.
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
require_once($_SERVER["DOCUMENT_ROOT"] . "/lib/antigate.php");

// Логирование
$fpLOG = @fopen($_SERVER["DOCUMENT_ROOT"]."/logs/c_friends_group_add.log", "a+");

// Массив с группами, в которых набирают друзей
$arGroups = array(-24261502,-34985835,-53966216);
//$arGroups = array(-24370682,-44723042,-27577645,-60235090,-72690157,-57221673,-75193916,-67526090); // -75193916 -моя

// Массив с сообщениями
$arMessages = array(
    "✔ ВСЕМ ПРИВЕТ ❤ ДОБАВЬТЕ ПОЖАЛУЙСТА МЕНЯ В ДРУЗЬЯ ❤",
    "❤ ДОБАВЬТЕ МЕНЯ ПОЖАЛУЙСТ В ДРУЗЬЯ! ДОБАВЛЮ ВСЕХ ВАС ТОЖЕ ❤",
    "❤ ПРИМУ В ДРУЗЬЯ ВСЕХ ВСЕХ ВСЕХ!!! ❤ ",
    "✔ Добавлю ВСЕХ добавлю Всех Добавляю Всех ✔",
    "❤ Привет народ! ❤ Добавляйтесь ко мне в друзья. Я Вас тоже всех обязательно добавлю! ✔",
    "✔ ДОБАВЛЮ ВСЕХ В ДРУЗЬЯ! ✔ В ПОДПИСЧИКИ НЕ КИНУ! ✔ УДАЛЯЮ ТЕХ, КТО УДАЛЯЕТ МЕНЯ! ✔",
    "❤ ЖДУ ДРУЗЕЙ, ДОБАВЛЯЙТЕСЬ! БУДУ РАД ВСЕМ ВАМ! ❤ В ПОДПИСЧИКАХ НЕ ОСТАВЛЯЮ! ✔",
);

// Вытаскиваем все аккаунты из базы и производим нужные действия
$arAccounts = array();
foreach ($DB->query("SELECT id, user_id, sex, access_token, secret FROM yoo_accounts WHERE soc='VK' AND status IN ('UNDERWAY','GROUP')") as $rowAcc)
{
    $arAccounts[$rowAcc['user_id']] = array('access_token'=>$rowAcc['access_token'], 'secret'=>$rowAcc['secret'], 'sex'=>$rowAcc['sex'], 'id'=>$rowAcc['id']);
}

// Для каждого пользователя инициализируем класс и выполняем нужные действия
foreach ($arAccounts as $user_id=>$access)
{
    // Создаем объект класса для пользователя
    $vkApi = new CVkRequest($DB, $access["id"], $access['access_token'], $access['secret'], true);

    // Случайным образом выбираем сообщение из массива
    $randKeyMessages = array_rand($arMessages);

    // Проходимся по всем группам и добавляем туда сообщение
    foreach($arGroups as $groupFriends)
    {
        postWallGroup($vkApi,$groupFriends,$arMessages[$randKeyMessages],$params['antigate_key'],$access["id"],$fpLOG);
    }
}

@fclose($fpLOG);

/**
 * Публикация записи на стене групп с проверкой капчи
 *
 * @param CVkRequest $vkApi
 * @param $groupID
 * @param $message
 * @param $antigate_key
 * @param $uid
 * @param $fpLOG
 */
function postWallGroup(CVkRequest $vkApi, $groupID, $message, $antigate_key, $uid, $fpLOG)
{
    // Делаем пост в группу на стену
    $postWall = $vkApi->postToWall($groupID, $message);

    //var_dump($postWall);

    // Если вернулся ответ
    if($postWall)
    {
        // Если VK вернул ошибку, то пишем ее в лог
        if(property_exists($postWall, "error"))
        {
            // Если требует капчи, отправляем ее на распознование и затем повторяем пост с параметрами капчи
            if($postWall->error->error_code == 14)
            {
                $captcha_img = $vkApi->getCaptchaImage($postWall->error->captcha_img);
                $captcha = recognize($captcha_img, $antigate_key, false, "antigate.com", 5, 60);
//                var_dump($captcha);
                if(strlen($captcha)>0)
                {
                    // устанавливаем параметры капчи
                    $arCaptcha = array(
                        "captcha_sid"=>$postWall->error->captcha_sid,
                        "captcha_key"=>$captcha
                    );
                    // вторая попытка публикации с введенной капчей
                    $repeatPostWall = $vkApi->postToWall($groupID, $message, $arCaptcha);
                    //var_dump($repeatPostWall);
                }
            }
            elseif($postWall->error->error_code == 17)
            {
                @fputs($fpLOG, "[".date("Y-m-d H:i:s")."] ERROR: USER_ID[".$uid."] - Validation required(17). Redirect URI:".$postWall->error->redirect_uri.".\r\n");
            }
        }
    }
}

