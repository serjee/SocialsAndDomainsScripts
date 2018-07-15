<?php
/**
 * Скрипт ищет пользователей по критериям и сохраняет в базу их ID
 * для дальнейшего использования.
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

// Счетчик попыток для запроса
$repeatCnt = 0;

// Вытаскиваем все аккаунты из базы и производим нужные действия
$rowAcc = $DB->prepare("SELECT id, user_id, access_token, secret FROM yoo_accounts WHERE id=:id");
$rowAcc->execute(array(':id'=>23)); // Берем только первый аккаунт
if($rowAcc = $rowAcc->fetch())
{
    // Создаем объект класса для пользователя
    $vkApi = new CVkRequest($DB, $rowAcc["id"], $rowAcc['access_token'], $rowAcc['secret'], true);

    // Вытаскиваем ID найденных пользователей
    $out = $vkApi->usersSearch(0, 1000);

    // Если вернула результат, то обрабатываем его
    if($out)
    {
        // Проверяем ответ и вносим в базу
        if(property_exists($out, "response"))
        {
            if (count($out->response->items) > 0)
            {
                // удаляем все записи из таблицы списка прокси
                $DB->exec("DELETE FROM yoo_users_searched WHERE list_id='1'");

                // записываем данные в таблицу
                $stmt = $DB->prepare("INSERT INTO yoo_users_searched (list_id, user_id, first_name, sex) VALUES (:list_id, :user_id, :first_name, :sex)");
                $stmt->bindParam(':list_id', $list_id);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':first_name', $first_name);
                $stmt->bindParam(':sex', $sex);
                foreach($out->response->items as $item)
                {
                    $list_id = 1; // возможно затем придется делать несколько списков
                    $user_id = $item->id;
                    $first_name = $item->first_name;
                    // пол
                    switch($item->sex)
                    {
                        case 1:
                            $sex = "F";
                            break;
                        case 2:
                            $sex = "M";
                            break;
                        default:
                            $sex = "N";
                    }

                    $stmt->execute();
                }
            }
        }
    }
}