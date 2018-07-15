<?php
/**
 * Скрипт привязывает аккаунты к прокси из списка.
 * Является частью системы "Раскрутки аккаунтов".
 * (вызывается врнучную)
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
require_once($_SERVER["DOCUMENT_ROOT"] . "/params.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/dbconn.php");

// Счетчик попыток для запроса
$arProxy = array();
$fpR = fopen("proxy_list.txt", "r");
if ($fpR)
{
    while (!feof($fpR))
    {
        $arProxy[] = fgets($fpR, 4096);
    }
}
else echo "Ошибка при открытии файла";
fclose($fpR);

$stmt = $DB->prepare("INSERT INTO yoo_proxy_vip (user_id, ip_port, login_pass, timefinish) VALUES (:user_id, :ip_port, :login_pass, :timefinish)");
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':ip_port', $ip_port);
$stmt->bindParam(':login_pass', $login_pass);
$stmt->bindParam(':timefinish', $timefinish);

// Вытаскиваем все аккаунты из базы и производим нужные действия
$i=0;
$rowAccRes = $DB->prepare("SELECT id FROM yoo_accounts WHERE soc='VK' AND status IN('UNDERWAY')");
$rowAccRes->execute();
while($rowAcc = $rowAccRes->fetch())
{
    // Создаем объект класса для пользователя
    $user_id = $rowAcc["id"];
    $ip_port = $arProxy[$i];
    $login_pass = "t2tIrBW4E:PnLtAU2Si";
    $timefinish = "2014-08-12 00:00:00";
    $stmt->execute();
    $i++;
}
