<?php
/**
 * Скрипт собирает e-mail адреса с сайтов
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
$DOCUMENT_ROOT = dirname(__FILE__); // getcwd();
require_once($DOCUMENT_ROOT . "/../params.php");
require_once($DOCUMENT_ROOT . "/../dbconn.php");

// возможные страницы сайта с контактами
$page = array('/', '/contacts/', '/contact/', '/about/contacts/', '/about/contact/', '/kontakty/', '/kontakti/', '/content/contacts/');

// Получаем список сайтов и обрабатываем их
$rsSite = $DB->Query("SELECT * FROM bitrix_sites_info");
while($rowSite = $rsSite->Fetch())
{
    // проходимся по возможным страницам из массива
    foreach($page as $p)
    {
        $url = "http://".str_replace("/", "", trim($rowSite["site"])).$p;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER,1);
        curl_setopt($curl, CURLOPT_COOKIEJAR, 'cookie.txt');  // Записываем cookie
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.106');
        curl_setopt($curl, CURLOPT_VERBOSE,1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        $mail_page = curl_exec($curl);
        curl_close($curl);
        preg_match_all('/([a-zA-Z0-9_\.-]+)@([a-zA-Z0-9_\.-]+)\.([a-zA-Z\.]{2,6})/isU', $mail_page, $mail_items);

        // Если есть e-mail, то записываем его в базу и выходим из цикла foreach
        if(isset($mail_items[0][0]) && strlen($mail_items[0][0])>0)
        {
            // добавляем информацию об опубликованной записи в базу
            $stmt = $DB->prepare("UPDATE bitrix_sites_info SET email=:email WHERE id=:id");
            $stmt->bindParam(':email', $mail_items[0][0]);
            $stmt->bindParam(':id', $rowSite["id"]);
            $stmt->execute();

            break;
        }
    }
}