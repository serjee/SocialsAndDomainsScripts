<?php
/**
 * Класс для отправки запросов к API через CURL
 *
 * формирует запросы и отправляет их через CURL,
 * содержит функции для отправки запросов через PROXY
 *
 * @author Sergey Bashkov ser.bsh@gmail.com
 * @version 1.0 (02.08.2014)
 */
class BaseCurl
{
    private $DB;
    private $ACC_ID;
    private $ACCESS_TOKEN;
    private $SECRET;
    private $API_URL = "http://api.vk.com/method/";
    private $ACCEPT = 'json';
    private $API_VERSION = '5.23';
    private $TEST_MODE = false;
    private $PROXY_URL = false;

    protected $LAST_ERROR = "";

    /**
     * Конструктор
     */
    public function __construct($DB, $id, $access_token, $secret=false, $proxy_url=false)
    {
        $this->DB = $DB;
        $this->ACC_ID = $id;
        $this->ACCESS_TOKEN = $access_token;
        $this->SECRET = $secret;
        $this->PROXY_URL = $proxy_url;
    }

    /**
     * Отправка запроса к API через CURL
     */
    protected function send_request($method_name, $parameters, $auth=false, $captcha=array())
    {
        // Проверяем, что параметры переданы
        if (!is_array($parameters))
        {
            throw new Exception('Ошибка: неверные данные post для формата json!');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->API_URL . $method_name);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_REFERER, @$_SERVER['HTTP_REFERER']);

        // Используем SSL-запрос, если он идет не через прокси
        if(!$this->PROXY_URL)
        {
            curl_setopt($ch, CURLOPT_SSLVERSION,3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }

        // Проверяем наличие параметров, дополняем их и устанавливаем для передачи
        if ($parameters && is_array($parameters))
        {
            // Добавляем версию API
            $parameters['v'] = $this->API_VERSION;

            // Позволяет выполнять запрос без включения приложения на всех пользователей
            if ($this->TEST_MODE) $parameters['test_mode'] = '1';

            // Добавляем токен, если необходим авторизованный запрос
            if ($auth) $parameters['access_token'] = $this->ACCESS_TOKEN;

            // Если есть каптча, добавляем эти параметры
            if (count($captcha)>0)
            {
                $parameters['captcha_sid'] = $captcha["captcha_sid"];
                $parameters['captcha_key'] = $captcha["captcha_key"];
            }

            // Переводим параметры из массива в строку
            $parameters = http_build_query($parameters);

            // SIG для доступа без https (не удалось настроить proxy через https)
            if ($auth && $this->SECRET)
            {
                $sig = md5("/method/".$method_name."?".$parameters.$this->SECRET);
                $parameters .= "&sig=".$sig;
            }

            // Устанавливаем параметры POST-запроса
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
            if ($this->ACCEPT === 'json')
            {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/' . $this->ACCEPT));
            }
        }

        // Агент (Referer)
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17');

        // Если запрос должен идти через прокси, устанавливаем параметры прокси сервера, предварительно их получив из базы
        if ($this->PROXY_URL)
        {
            //$proxy_host_port = $this->get_random_proxy_with_update();
            $proxy_data = $this->get_proxy_vip();
            if (count($proxy_data)>0)
            {
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                curl_setopt($ch, CURLOPT_PROXY, $proxy_data["ip_port"]);
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_data["login_pass"]);
            }
            else
            {
                return false; // выходим с лучае, если прокси не удалось получить (защита от использования прямого IP)
            }
        }

        // Получаем результаты запроса
        $out = curl_exec($ch);
        $curl_error = curl_errno($ch);
        $info = curl_getinfo($ch);

        // если есть ошибки, возвращаем false
        if ($curl_error)
        {
            $this->LAST_ERROR = 'HTTP ERROR: ' . $curl_error;
            return false;
        }

        // успешно, - 200. В остальных случаях возвращаем false
        if ($info['http_code']=='200')
        {
            return json_decode($out);
        }
        else
        {
            $this->LAST_ERROR = 'HTTP CODE ERROR: ' . $info['http_code'];
        }

        return false;
    }

    /**
     * Получаем параметры прокси, привязанного к аккаунту
     *
     * @return array
     */
    private function get_proxy_vip()
    {
        $rowAccRes = $this->DB->prepare("SELECT * FROM yoo_proxy_vip WHERE user_id=:user_id");
        $rowAccRes->execute(array(':user_id'=>$this->ACC_ID));
        if($rowAcc = $rowAccRes->fetch())
        {
            return array(
                "ip_port"=>$rowAcc["ip_port"],
                "login_pass"=>$rowAcc["login_pass"]
            );
        }
        return array();
    }

    /**
     * (Не используем) Получаем рандомный прокси-сервер для подключения через него
     */
    protected function get_random_proxy_with_update()
    {
        // Определяем количество актуальных прокси-серверов в нашей базе
        $now = date('Y-m-d H:i:s');

        // Получаем количество записей актуальных прокси
        $resultProxy = $this->DB->prepare("SELECT * FROM yoo_proxylist WHERE timestamp > :timestamp", array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
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
     * (Не используем) Случайным образом выбираем и возвращаем один прокси-адрес из базы
     *
     * @return bool|string
     */
    private function get_random_proxy()
    {
        // Случайным образом получаем один прокси из базы и возвращаем его
        $resultRndProxy = $this->DB->prepare("SELECT ip, port FROM yoo_proxylist ORDER BY RAND() LIMIT 1");
        $resultRndProxy->execute();
        if ($get_proxy = $resultRndProxy->fetch())
        {
            return trim($get_proxy['ip'].':'.$get_proxy['port']);
        }
        return false;
    }

    /**
     * (Не используем) Обновляет список прокси в базе, если они были добавлены менее 3 минут назад (считаются уже устаревшими)
     */
    private function proxy_update()
    {
        // Удаляем все записи из таблицы списка прокси
        $this->DB->exec("DELETE FROM yoo_proxylist");

        // Обновляем список
        if( $curl = curl_init() )
        {
            curl_setopt($curl, CURLOPT_URL, $this->PROXY_URL);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            $arProxy = json_decode(curl_exec($curl));
            curl_close($curl);

            // Количетство новых полученных прокси
            $prCount = count($arProxy);

            // Если больше нуля, то получаем от hideme.ru через их API список новых проксей
            if ($prCount > 0)
            {
                foreach($arProxy as $proxy)
                {
                    // Проверяем на валидность и заносим в базу
                    $fp = @fsockopen ($proxy->ip, $proxy->port, $errno, $errstr, 5);
                    if ($fp)
                    {
                        $resultInsertProxy = $this->DB->prepare("INSERT INTO yoo_proxylist SET ip=:ip, port=:port, timestamp=DATE_ADD(NOW(), INTERVAL 3 MINUTE)");
                        $resultInsertProxy->execute(array(":ip"=>$proxy->ip, ":port"=>$proxy->port));

                        fclose ($fp);
                    }
                }
            }
        }
    }
}