<?php
/**
 * Скрипт парсит последнюю страницу с объявлениями и заносит новые в базу
 * Является частью системы "Парсеры".
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
$DOCUMENT_ROOT = dirname(__FILE__); // getcwd();
require_once($DOCUMENT_ROOT . "/params.php");
require_once($DOCUMENT_ROOT . "/dbconn.php");
require_once($DOCUMENT_ROOT . "/class/CAvitoRequest.php");

// Создаем объект класса
$avitoObj = new CAvitoRequest($DB, true);

// Находим все объявления москва-сдам (фильтр - ссылка)
$avito_content = $avitoObj->get_avito_content('/moskva/kvartiry/sdam');
preg_match_all('/<article class=\"b-item\s+(item-highlight)?\">(.*?)<\/article>/is', $avito_content, $avito_items, PREG_PATTERN_ORDER);

// Перебираем массив с объявлениями, вытаскиваем содержимое каждого и обрабатываем
if(isset($avito_items[2]) && count($avito_items[2])>0)
{
    //$ic = 0;
    foreach($avito_items[2] as $item)
    {
        //if ($ic>0) break; // для дебага делаем только один проход
        //$ic++;
        // маркер ошибок при парсинге текущего объявления
        $itemErrors = false;

        // начальные параметры объявления из списка объявлений
        preg_match('/<h3 class=\"item-header one-line-text\">(.*?)<span/is', $item, $iHead);    // тип квартиры
        preg_match('/<span class=\"nobr\">(.*?)<\/span/is', $item, $iNobr);                     // этажность
        preg_match('/<span class=\"info-text info-metro-district\">(.*?)<\/span>/is', $item, $iMetro);  // метро
        preg_match('/<span class=\"info-address info-text\">(.*?)<\/span>/is', $item, $iAddr);  // адрес
        preg_match('/<div class=\"info-date info-text\">(.*?)<\/div>/is', $item, $iDate);       // дата
        preg_match('/<a href=\"(.*?)\" class=\"item-link\"><\/a>/is', $item, $iLink);           // ссылка

        // проверка начальных параметров на ошибки, если есть, то переходим к парсингу следующего объявления
        if (!isset($iHead[1]) || strlen($iHead[1])==0) continue;
        if (!isset($iNobr[1]) || strlen($iNobr[1])==0) continue;
        if (!isset($iMetro[1]) || strlen($iMetro[1])==0) continue;
        if (!isset($iAddr[1]) || strlen($iAddr[1])==0) continue;
        if (!isset($iDate[1]) || strlen($iDate[1])==0) continue;
        if (!isset($iLink[1]) || strlen($iLink[1])==0) continue;

        // переходим на страницу подробного описания объявления
        $itemUrl = trim($iLink[1]);
        var_dump($itemUrl);
        $content_item = $avitoObj->get_avito_content($itemUrl);

        // парсим с нее содержимое
        preg_match('/<div class=\"item-id\">Объявление №(.*?)<\/div>/siu', $content_item, $iItem); // номер объявления
        preg_match('/<div class=\"description-wrapper\">(.*?)<\/div>/si', $content_item, $iDesc);   // текст объявления (strip_tags(текст))
        preg_match('/<div class=\"person-name\">(.*?)<\/div>/siu', $content_item, $iPersonName);    // имя
        preg_match('/<span class=\"price-value\">(.*?)<\/span>/si', $content_item, $iPrice);        // цена
        preg_match('/<a class=\"person-action(.*?)\" href=\"(.*?)\" title=\"Телефон продавца\"/siu', $content_item, $iPhoneLink); // ссылка получения номера телефона

        // проверка параметров на ошибки у детальной страницы, если есть, то переходим к парсингу следующего объявления
        if (!isset($iItem[1])) continue;
        if (($intItem = filter_var(trim($iItem[1]), FILTER_SANITIZE_NUMBER_INT)) < 1) continue;
        if(!isset($iPersonName[1])) continue;
        if(isset($iPersonName[1]) && preg_match('/агентство/siu', $iPersonName[1])) continue; // агентов не обрабатываем
        if (!isset($iPrice[1]) || strlen($iPrice[1])==0) continue;
        if(!isset($iPhoneLink[2]) || strlen($iPhoneLink[2])==0) continue;

        var_dump("SUCCESS");

        // проверка наличия записи в базе
        $rowParse = $DB->prepare("SELECT count(*) as cnt FROM rilty_parse_sdam_mos WHERE item=:item");
        $rowParse->execute(array(':item'=>$intItem)); // проверяем наличие в базе по ID объявления
        if(!$rowParse->fetch()) continue;

        var_dump("ACTUALL");
        var_dump($iPhoneLink[2]);

        // получаем телефон
        $iPhone = $avitoObj->get_phone_number(trim($iPhoneLink[2]),$itemUrl);
        if(strlen($iPhone)<1) continue;

        var_dump("PHONE");

        // собираем все фото объявления в массив (отдельно главную и затем остальные в цикле)
        $arFotos = array();
        preg_match('/<img src=\"(.*?)\" alt=\"(.*?)\" class=\"photo-self\"/is', $content_item, $iFotoM); // первая фото
        if(isset($iFotoM[1]) && strlen($iFotoM[1])>0) $arFotos[] = $iFotoM[1];
        preg_match_all('/<li class=\"photo-container\"><span class=\"loader img-pseudo\" data-img-src=\"(.*?)\" data-img-alt=\"(.*?)\" data-img-class=\"photo-self\"/is', $content_item, $iPhotos, PREG_PATTERN_ORDER);
        if(isset($iPhotos[1]) && is_array($iPhotos[1]) && count($iPhotos[1])>0)
        {
            foreach($iPhotos[1] as $iFoto)
            {
                $arFotos[] = $iFoto;
            }
        }

        // если в массиве есть хотя бы одна фото, то загружаем их
        if(count($arFotos)>0)
        {
            // создаем нужные папки для хранения фото
            $fotoBaseDir = $DOCUMENT_ROOT. '/upload/avito/sdam';
            $fotoItemDir = $fotoBaseDir . '/' . strval($intItem);
            if (!file_exists($fotoItemDir))
            {
                mkdir($fotoItemDir, 0755, true);
            }

            // обходим все url фото и сохраняем их на диске в указанной папке (в массиве пересохраяем путь к файлам на диске)
            foreach($arFotos as $kf=>$pathFoto)
            {
                // загружаем фото
                $arFotos[$kf] = $avitoObj->save_image_by_url("http:".$pathFoto, $fotoItemDir, pathinfo($pathFoto, PATHINFO_BASENAME));

                // обрезаем его, если не получилось обрезать, убираем линк из массива
                if(!$avitoObj->crop_image($arFotos[$kf]))
                {
                    unset($arFotos[$kf]);
                }
                else
                {
                    $arFotos[$kf] = pathinfo($arFotos[$kf], PATHINFO_BASENAME); // перезаписываем элементы массива, запоминая только имена файлов
                }
            }
        }

        // если удалось загрузить и обработать изображения, формируем строку из их имен для записи в базу
        $strFotos = "";
        if(count($arFotos)>0)
        {
            $strFotos = implode(",", $arFotos);
        }

        // создаем массив для хранения данных и сохраняем спарсенные значения
        $arItem = array(
            "item" => $intItem,
            "date" => str_replace('&nbsp;',' ', trim($iDate[1])),
            "title" => str_replace('&nbsp;',' ', trim($iHead[1]) . ' ' . trim($iNobr[1])),
            "addr" => str_replace('&nbsp;',' ', trim($iMetro[1]) . ", г.Москва, " . trim($iAddr[1])),
            "descipt" => "",
            "photos" => $strFotos,
            "price" => str_replace('&nbsp;',' ', trim($iPrice[1])),
            "contact_name" => str_replace('&nbsp;',' ', trim($iPersonName[1])),
            "contact_phone" => $iPhone,
        );
        if(isset($iDesc[1]) && strlen($iDesc[1])>0) $arItem["descipt"] = str_replace('&nbsp;',' ', strip_tags(trim($iDesc[1]))); // если есть описание, добавляем его в массив данных

        //print_r($arItem);

        // запись объявления в базу
        $rInsertItem = $DB->prepare("INSERT INTO rilty_parse_sdam_mos (item, date, title, addr, descipt, photos, price, contact_name, contact_phone) VALUES (:item, :date, :title, :addr, :descipt, :photos, :price, :contact_name, :contact_phone)");
        $rInsertItem->bindParam(':item', $arItem["item"]);
        $rInsertItem->bindParam(':date', $arItem["date"]);
        $rInsertItem->bindParam(':title', $arItem["title"]);
        $rInsertItem->bindParam(':addr', $arItem["addr"]);
        $rInsertItem->bindParam(':descipt', $arItem["descipt"]);
        $rInsertItem->bindParam(':photos', $arItem["photos"]);
        $rInsertItem->bindParam(':price', $arItem["price"]);
        $rInsertItem->bindParam(':contact_name', $arItem["contact_name"]);
        $rInsertItem->bindParam(':contact_phone', $arItem["contact_phone"]);
        $rInsertItem->execute();
    }
}
