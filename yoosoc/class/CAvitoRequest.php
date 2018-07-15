<?php
/**
 * Класс для работы с AVITO.RU
 *
 * @author Sergey Bashkov ser.bsh@gmail.com
 * @version 1.0 (16.08.2014)
 */
class CAvitoRequest
{
    private $DB;
    private $PROXY = false;
    private $PROXY_DATA;
    private $PROXY_URL = 'http://hideme.ru/api/proxylist.txt?out=plain&country=&maxtime=999&type=s&code=';

    /**
     * Конструктор
     */
    public function __construct($DB, $proxy=false)
    {
        $this->DB = $DB;
        $this->PROXY = $proxy;
        $this->PROXY_DATA = "";
    }

    /**
     * Обрезает на каринке логотип снизу (по высоте на 40 пикселей - именно столько он занимает на Авито)
     *
     * @param $filename путь к файлу с исходной картинкой
     * @return bool
     */
    public function crop_image($filename)
    {
        list($w, $h, $type) = getimagesize($filename); // Получаем размеры и тип изображения (число)
        $types = array("", "gif", "jpeg", "png"); // массив с типами изображений
        $ext = $types[$type]; // зная "числовой" тип изображения, узнаём название типа
        if ($ext=="jpeg")
        {
            $h = $h-40; // минусуем 40px по высоте, чтобы вырезать эту область снизу картинки (убрать логотип авито)
            $source = imageCreateFromJPEG($filename); // Создаём дескриптор для работы с исходным изображением
            $thumb = imagecreatetruecolor($w, $h);
            $bgc = imagecolorallocate($thumb, 255, 255, 255);
            imagefilledrectangle($thumb, 0, 0, $w, $h, $bgc);
            imagecopyresized($thumb, $source, 0, 0, 0, 0, $w, $h, $w, $h);
            imagejpeg($thumb, $filename);
        }
        else
        {
            return false; // формат изображения недопустимый
        }

        return true;
    }

    /**
     * Получаем список объявлений с АВИТО по указанному фильтру ($url)
     * (используем прокси)
     *
     * @param $url
     * @param bool $getPhone
     * @param string $refererUrl
     * @return bool|mixed
     */
    public function get_avito_content($url, $getPhone=false, $refererUrl="")
    {
        $url = 'https://m.avito.ru'.$url;
        if($getPhone) $url = $url . "?async";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER,1);
        ///curl_setopt($curl, CURLOPT_SSLVERSION,2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_COOKIEJAR, 'cookie.txt');  // Записываем cookie
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.106');
        curl_setopt($curl, CURLOPT_VERBOSE,1);
        if($getPhone) curl_setopt($curl, CURLOPT_REFERER, $refererUrl); // при запросе телефона указываем реферера
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);

        // используем прокси, если нужно
        if(!$this->curl_proxy_if_need($curl)) return false;

        var_dump($this->PROXY_DATA);

        $text = curl_exec($curl);
        $curl_error = curl_errno($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        //var_dump($curl_error);
        //var_dump($info);

        return $text;
    }

    /**
     * Выполнение запроса через ПРОКСИ если это указано настройками
     *
     * @param $curl
     * @return bool
     */
    private function curl_proxy_if_need(&$curl)
    {
        // Если запрос должен идти через прокси, устанавливаем параметры прокси сервера, предварительно их получив из базы
        if ($this->PROXY)
        {
            // проверяем, выбран ли уже прокси при предыдущем запросе
            if(strlen($this->PROXY_DATA)>0)
            {
                $proxy_string = $this->PROXY_DATA;
            }
            else
            {
                $proxy_string = $this->PROXY_DATA = $this->get_random_proxy_with_update();
            }

            // если успешно получили прокси
            if (strlen($proxy_string)>0)
            {
                $this->PROXY_DATA = $proxy_string;
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
                curl_setopt($curl, CURLOPT_PROXY, $proxy_string);
            }
            else
            {
                return false; // выходим с лучае, если прокси не удалось получить (защита от использования прямого IP)
            }
        }

        return true;
    }

    /**
     * Получаем номер телефона для объявления с АВИТО
     *
     * @param $getPhoneUrl
     * @param $refererUrl
     * @return string
     */
    public function get_phone_number($getPhoneUrl, $refererUrl)
    {
        // получаем телефон через curl запрос
        $telefon = $this->get_avito_content($getPhoneUrl, true, $refererUrl);

        // парсим заголовок и возвращаем номер телефона
        preg_match('/\{\"phone\"\:\"(.*?)\"\}/is', $telefon, $iPhone); // телефон
        if(isset($iPhone[1]))
            return trim($iPhone[1]);

        return "";
    }

    /**
     * Сохраняем картинку капчи по ее URL и возвращаем к ней путь
     *
     * @param $url
     * @param $dir
     * @param $file
     * @internal param $path
     * @return string
     */
    public function save_image_by_url($url, $dir, $file)
    {
        $filePath = $dir . '/' . $file;
        if (file_exists($filePath)) unlink($filePath); // удаляем старый файл если он есть

        $ch = curl_init($url);
        $fp = fopen($filePath, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        sleep(1); // задержка на 1 секунду

        return $filePath;
    }

    /**
     * Получаем рандомный прокси-сервер для подключения через него
     */
    protected function get_random_proxy_with_update()
    {
        // Определяем количество актуальных прокси-серверов в нашей базе
        $now = date('Y-m-d H:i:s');

        // Получаем количество записей актуальных прокси
        $resultProxy = $this->DB->prepare("SELECT * FROM rilty_proxylist WHERE timestamp > :timestamp", array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $resultProxy->execute(array(":timestamp"=>$now));
        $cntProxy = $resultProxy->fetchColumn();

        // Если наш список прокси-серверов в базе устарел, то обновляем его
        if ($cntProxy < 1)
        {
            $this->proxy_update();
        }

        // Случайным образом возвращем один из прокси адресов
        return $this->get_random_proxy();
    }

    /**
     * Случайным образом выбираем и возвращаем один прокси-адрес из базы
     *
     * @return bool|string
     */
    private function get_random_proxy()
    {
        // Случайным образом получаем один прокси из базы и возвращаем его
        $resultRndProxy = $this->DB->prepare("SELECT ip, port FROM rilty_proxylist ORDER BY RAND() LIMIT 1");
        $resultRndProxy->execute();
        if ($get_proxy = $resultRndProxy->fetch())
        {
            return trim($get_proxy['ip'].':'.$get_proxy['port']);
        }
        return false;
    }

    /**
     * Обновляет список прокси в базе, если они были добавлены менее 3 минут назад (считаются уже устаревшими)
     */
    private function proxy_update()
    {
        // Удаляем все записи из таблицы списка прокси
        $this->DB->exec("DELETE FROM rilty_proxylist");

        // Обновляем список
        if( $curl = curl_init() )
        {
            curl_setopt($curl, CURLOPT_URL, $this->PROXY_URL);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            $plainProxy = curl_exec($curl);
            curl_close($curl);

            $arProxies = array();
            $arProxyList = explode("\r\n", $plainProxy);
            foreach($arProxyList as $proxy_port)
            {
                if(strlen($proxy_port)>0)
                    $arProxies[] = explode(":", $proxy_port);
            }

            // Количетство новых полученных прокси
            $prCount = count($arProxies);

            // Если больше нуля, то получаем от hideme.ru через их API список новых проксей
            if ($prCount > 0)
            {
                foreach($arProxies as $proxyPartArray)
                {
                    $proxy = new stdClass();
                    $proxy->ip = trim($proxyPartArray[0]);
                    $proxy->port = trim($proxyPartArray[1]);

                    // Первая проверка прокси на валидность
                    $fp = @fsockopen ($proxy->ip, $proxy->port, $errno, $errstr, 5);
                    if ($fp)
                    {
                        // Вторая проверка прокси на доступ к АВИТО с текущего IP
                        $cv = curl_init();
                        curl_setopt($cv, CURLOPT_URL, 'https://m.avito.ru');
                        curl_setopt($cv, CURLOPT_HEADER,1);
                        ///curl_setopt($cv, CURLOPT_SSLVERSION,2);
                        curl_setopt($cv, CURLOPT_SSL_VERIFYPEER, FALSE);
                        curl_setopt($cv, CURLOPT_SSL_VERIFYHOST, FALSE);
                        curl_setopt($cv, CURLOPT_COOKIEJAR, 'cookie.txt');  // Записываем cookie
                        curl_setopt($cv, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.106');
                        curl_setopt($cv, CURLOPT_VERBOSE,1);
                        curl_setopt($cv, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($cv, CURLOPT_CONNECTTIMEOUT, 60);
                        curl_setopt($cv, CURLOPT_TIMEOUT, 60);
                        curl_setopt($cv, CURLOPT_HTTPPROXYTUNNEL, true);
                        curl_setopt($cv, CURLOPT_PROXY, $proxy->ip.":".$proxy->port);
                        curl_exec($cv);
                        $info = curl_getinfo($cv);
                        $curl_error = curl_errno($cv);
                        curl_close($cv);

                        //var_dump($curl_error);
                        //var_dump($info);

                        if($info["http_code"]==200)
                        {
                            // Запоминаем прокси в базу на 5 минут
                            $resultInsertProxy = $this->DB->prepare("INSERT INTO rilty_proxylist SET ip=:ip, port=:port, timestamp=DATE_ADD(NOW(), INTERVAL 5 MINUTE)");
                            $resultInsertProxy->execute(array(":ip"=>$proxy->ip, ":port"=>$proxy->port));

                            fclose ($fp);
                        }
                    }
                }
            }
        }
    }
}