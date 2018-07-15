<?php

class CVkSimple
{
    private $DB;
    private $TOKEN;
    private $API_VERSION = "5.24";

    /**
     * Конструктор
     */
    public function __construct($DB, $token)
    {
        $this->DB = $DB;
        $this->TOKEN = $token;
    }

    /**
     * (Вконтакте) Добавляет запись на стене указанного пользователя
     *
     * @param $owner_id int ID владельца стены
     * @param $message string Сообщение
     * @param $attachments string
     * @param bool $linkItemPost
     * @return bool
     */
    public function postToWall($owner_id, $message, $attachments="", $linkItemPost=false)
    {
        $params = array(
            "owner_id" => $owner_id, // id стены группы
            "message" => $message, // сообщение
            "from_group" => 1, // публикуется от имени группы
        );

        // Если есть аттачмент, прикрепляем его
        if($attachments!="")
        {
            $params["attachments"] = $attachments;
        }

        // Если есть ссылка, добавляем ее к сообщению
        if($linkItemPost)
        {
            $params["message"] = $message ."\r\n" . $linkItemPost;
        }

        return self::send_request("wall.post", $params);
    }

    /**
     * (Вконтакте) Сохраняет фотографию на стене группы
     *
     * @param $group_id
     * @param $photo
     * @param $server
     * @param $hash
     * @return bool|mixed
     */
    public function photosSaveWallPhoto($group_id, $photo, $server, $hash)
    {
        $params = array(
            "group_id" => $group_id, // идентификатор сообщества, на стену которого нужно сохранить фотографию
            "photo" => $photo, // параметр, возвращаемый в результате загрузки фотографии на сервер
            "server" => $server, // параметр, возвращаемый в результате загрузки фотографии на сервер
            "hash" => $hash, // параметр, возвращаемый в результате загрузки фотографии на сервер
        );

        return self::send_request("photos.saveWallPhoto", $params);
    }

    /**
     * (Вконтакте) Возвращаем адрес сервера для загрузки изображений
     *
     * @param $group_id
     * @return bool|mixed
     */
    public function photosGetWallUploadServer($group_id)
    {
        $params = array(
            "group_id" => $group_id, // id стены группы
        );

        return self::send_request("photos.getWallUploadServer", $params);
    }

    /**
     * (Вконтакте) Отправляет изображение на указанный URL методом post
     *
     * @param $upload_url
     * @param $image_url
     * @return array()
     */
    public function postUploadImage($upload_url, $image_url)
    {
        // отправляем POST запрос с изображением для загрузки на сервер
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $upload_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array("photo"=>'@'.$image_url));

        // массив ответа
        if (($upload = curl_exec($ch)) !== false)
        {
            curl_close($ch);
            $upload = json_decode($upload);

            return array(
                'server' => $upload->server,
                'photo' => $upload->photo,
                'hash' => $upload->hash,
            );
        }

        return array();
    }

    /**
     * Отправка запроса к API VK через CURL
     */
    private function send_request($method_name, $parameters)
    {
        // Проверяем, что параметры переданы
        if (!is_array($parameters) || count($parameters)<1)
        {
            //throw new Exception('Ошибка: неверные данные post для формата json!');
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.vk.com/method/" . $method_name);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_REFERER, @$_SERVER['HTTP_REFERER']);

        // Используем SSL-запрос
        //curl_setopt($ch, CURLOPT_SSLVERSION,3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // Добавляем версию API
        $parameters['v'] = $this->API_VERSION;

        // Добавляем токен
        $parameters['access_token'] = $this->TOKEN;

        // Переводим параметры из массива в строку
        $parameters = http_build_query($parameters);

        // Устанавливаем параметры POST-запроса
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));

        // Агент (Referer)
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17');

        // Получаем результаты запроса
        $out = curl_exec($ch);
        $curl_error = curl_errno($ch);
        $info = curl_getinfo($ch);

        // если есть ошибки, возвращаем false
        if ($curl_error)
        {
            //$this->LAST_ERROR = 'HTTP ERROR: ' . $curl_error;
            return false;
        }

        // успешно, - 200. В остальных случаях возвращаем false
        if ($info['http_code']=='200')
        {
            return json_decode($out);
        }

        return false;
    }
}