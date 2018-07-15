<?php
/**
 * Скрипт парсит клиентов битрикса с его сайта
 * Является частью системы "Парсеры".
 */

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
@ini_set('memory_limit', '1024M');
@set_time_limit(0);
@ini_set('max_execution_time',0);
@ini_set('set_time_limit',0);

header('Content-Type: text/html; charset=windows-1251');
@ob_end_flush();

// Подключаем необходимые файлы
$DOCUMENT_ROOT = dirname(__FILE__); // getcwd();
require_once($DOCUMENT_ROOT . "/../params.php");
require_once($DOCUMENT_ROOT . "/../dbconn.php");

$fp = fopen(dirname(__FILE__).'/../bx_grab.txt', 'a+');

$start_page = 1;
$end_page = 77; // 151
$arResultData = array();
for ($i=$start_page;$i<=$end_page;$i++)
{
    //$url = 'http://www.1c-bitrix.ru/products/cms/projects/index.php?arFilter1_pf%5BCLIENT_FIELD%5D=&arFilter1_pf%5BTYPE%5D%5B0%5D=359&arFilter1_pf%5Bedition%5D=91&set_filter=Y&PAGEN_1='.$i;
    $url = 'http://www.1c-bitrix.ru/products/cms/projects/index.php?arFilter1_pf%5BCLIENT_FIELD%5D=&arFilter1_pf%5BTYPE%5D%5B0%5D=359&arFilter1_pf%5Bedition%5D=92&set_filter=Y&PAGEN_1='.$i;

    // получаем контент страницы со списком магазинов
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER,1);
    curl_setopt($curl, CURLOPT_COOKIEJAR, 'cookie.txt');  // Записываем cookie
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.106');
    curl_setopt($curl, CURLOPT_VERBOSE,1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
    $bx_list = curl_exec($curl);
    curl_close($curl);

    // парсим каждый магазин из списка и вытскиваем нужные данные
    preg_match_all('/<a href=\"(.*?)\" class=\"list_project_name\">(.*?)<\/a>/i', $bx_list, $bx_items, PREG_PATTERN_ORDER);
    foreach($bx_items[2] as $k=>$iName)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://www.1c-bitrix.ru".$bx_items[1][$k]);
        curl_setopt($curl, CURLOPT_HEADER,1);
        curl_setopt($curl, CURLOPT_COOKIEJAR, 'cookie.txt');  // Записываем cookie
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.106');
        curl_setopt($curl, CURLOPT_VERBOSE,1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        $b_detail = curl_exec($curl);
        curl_close($curl);

        //var_dump($b_detail);
        //var_dump($bx_items[1][$k]);

        // на детальной находим необходимые нам поля
        preg_match_all('/<b>(.*?)<\/b>(.*?)<br/is', $b_detail, $iDet);    // url
        $arDetailInfo = array(
            "name" => $iName,
        );
        //var_dump($iDet[2]);
        for ($j=1;$j<=3;$j++)
        {
            //print_r($iDet[2][$j]);
            if(isset($iDet[2][$j]))
            {
                switch($j)
                {
                    case 1:
                        preg_match('/<a href=\"(.*?)\" target=\"_blank\">(.*?)<\/a>/is', trim($iDet[2][$j]), $iUrl);
                        $arDetailInfo["url"] = trim($iUrl[2]);
                        break;
                    case 2:
                        $arDetailInfo["otr"] = trim($iDet[2][$j]);
                        break;
                    case 3:
                        $arDetailInfo["typ"] = trim($iDet[2][$j]);
                        break;
                    case 4:
                        preg_match('/<a href=\"(.*?)\" target=\"_blank\">(.*?)<\/a>/i', trim($iDet[2][$j]), $iRed);
                        $arDetailInfo["red"] = trim($iRed[2]);
                        break;
                }
            }
        }
        //var_dump($arDetailInfo);
        @fwrite($fp, implode("|", $arDetailInfo)."\r\n");
        //$arResultData[] = implode("|", $arDetailInfo);
        sleep(3); // задержка по окончании обработки каждого элемента на странице (24 шт на каждой странице)
        //break;
    }

    //break;
    sleep(120); // задержка перед обработкой следующей страницы в секундах
}

fclose($fp);
//echo "<pre>";
//print_r($arResultData);

//echo "done!";