<?php
/**
 * Скрипт ставит лайк последней записи на стене пользователей из списка.
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

    // Вытаскиваем жертв из базы (пользователей для добавления в друзья)
    $arUsers = array();
    foreach ($DB->query("SELECT user_id, first_name, sex FROM yoo_users_searched WHERE processed='N'") as $rowUser)
    {
        // Сохраняем всех пользователей в массив (их у нас все равно не больше 1000 чел)
        $arUsers[] = $rowUser;
    }

    // выбираем 5 (пять) произвольных пользователей и отправляем им приглашения в друзья
    if(count($arUsers)>0)
    {
        $userCounter = 5; // Столько количество попыток добавления в друзья будет делаться при каждом вызове скрипта
        if (count($arUsers)<5) { $userCounter = count($arUsers); } // Если аккаунтов меньше 5
        for($i=1; $i<=$userCounter; $i++)
        {
            // Выбираем произвольного пользователя
            $randKeyUsers = array_rand($arUsers);
            $rndUser = $arUsers[$randKeyUsers];

            // Отправляем заявку на добавление в друзья
            $wallUser = $vkApi->wallGet($rndUser["user_id"]);

            // Если вернулся ответ
            if($wallUser)
            {
                // Если VK вернул ошибку, то пишем ее в лог
                if(property_exists($wallUser, "response"))
                {
                    // Если на стене пользователя есть запись для лайка
                    if(count($wallUser->response->items)>0)
                    {
                        // Лайкаем запись
                        $likesUser = $vkApi->likesAdd($rndUser["user_id"],$wallUser->response->items[0]->id);
                        //var_dump($likesUser);
                    }
                }
            }
            else
            {
                // откат счетчика в случае ошибки (пока отключили)
                //$i--;
            }

            sleep(5); // задержка 5 секунд перед каждым новым добавлением в друзья
        }
    }
}
