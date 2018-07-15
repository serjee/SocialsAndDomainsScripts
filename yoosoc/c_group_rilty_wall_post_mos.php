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
$DOCUMENT_ROOT = dirname(__FILE__); // getcwd();
require_once($DOCUMENT_ROOT . "/params.php");
require_once($DOCUMENT_ROOT . "/dbconn.php");
require_once($DOCUMENT_ROOT . "/class/CVkSimple.php");

// Группа и доступ к ней
$group_id = 19677234; // rilty
$token = "045d9437210e3db747f4a11f941a3aeccf819dc3c1187ca3e8fcea87a852cfe551b218b35106be1a0043f";

// Создаем объект класса для публикации
$vkApi = new CVkSimple($DB, $token);

// Получаем адрес сервера для загрузки изображений
$uploadServer = $vkApi->photosGetWallUploadServer($group_id);
if($uploadServer)
{
    // если ответ с адресом сервера на загрузку пришел
    if(property_exists($uploadServer, "response"))
    {
        // Получаем список объявлений, которые еще не были опубликованы
        $rsSdam = $DB->Query("SELECT id,item,title,addr,descipt,photos,price,contact_name,contact_phone FROM rilty_parse_sdam_mos WHERE published='N'");
        while($rowSdam = $rsSdam->Fetch())
        {
            // переменные
            $arSrcPictures = array();
            $attachment = array();
            $strAttachment = "";

            // извлекаем имена фото
            if(strlen($rowSdam["photos"])>0)
            {
                $arSrcPictures = explode(",", $rowSdam["photos"]);
            }

            // формируем массив аттачментов всех изображений из массива путей для картинок элемента
            if(count($arSrcPictures)>0)
            {
                $upload_url = $uploadServer->response->upload_url;
                foreach($arSrcPictures as $srcPicture)
                {
                    // каждое изображение загружаем на сервер
                    $image_url = $DOCUMENT_ROOT . '/upload/avito/sdam/' . $rowSdam["item"] . '/' . $srcPicture;
                    $arImgUploaded = $vkApi->postUploadImage($upload_url, $image_url);

                    // публикуем изображение на стене группы
                    if(count($arImgUploaded)>0)
                    {
                        $imgWallPhoto = $vkApi->photosSaveWallPhoto($group_id, $arImgUploaded["photo"], $arImgUploaded["server"], $arImgUploaded["hash"]);
                        if($imgWallPhoto)
                        {
                            if(property_exists($uploadServer, "response"))
                            {
                                $attachment[] = "photo".$imgWallPhoto->response[0]->owner_id."_".$imgWallPhoto->response[0]->id;
                            }
                        }
                    }
                }
                // Массив аттачментов преобразуем в строку
                if (count($attachment)>0)
                {
                    $strAttachment = implode(",", $attachment);
                }
            }

            // Формируем текст сообщения
            $messageText = $rowSdam["title"]."\r\n".$rowSdam["addr"]."\r\n".$rowSdam["descipt"]."\r\nЦена: ".$rowSdam["price"]."\r\n".$rowSdam["contact_name"]." (".$rowSdam["contact_phone"].")";

            // Публикуем запись на стене (текст и/или изображения)
            $postWall = $vkApi->postToWall(-$group_id, $messageText, $strAttachment);
            if($postWall)
            {
                // ожидаем ответа с id размещенной записи
                if(property_exists($postWall, "response"))
                {
                    // если запись опубликовалась, берем ID записи
                    $post_id = intval($postWall->response->post_id);
                    if($post_id>0)
                    {
                        // добавляем информацию об опубликованной записи в базу
                        $stmt = $DB->prepare("UPDATE rilty_parse_sdam_mos SET published='Y' WHERE id=:id");
                        $stmt->bindParam(':id', $rowSdam["id"]);
                        $stmt->execute();
                    }
                }
            } // end post;

            sleep(30); // задержка 30 секунд
        }
    }
}