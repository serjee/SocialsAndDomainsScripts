<?
// Подключаем базовый класс
require_once(getcwd() . "/class/BaseCurl.php");

/**
 * Класс для работы с API ВКОНТАКТЕ
 *
 * формирует необходимые функции и делает запросы через CURL
 *
 * @author Sergey Bashkov ser.bsh@gmail.com
 * @version 1.0 (02.08.2014)
 */
class CVkRequest extends BaseCurl
{
    /**
     * Конструктор
     */
    public function __construct($DB, $id, $access_token, $secret=false, $proxy_url=false)
    {
        parent::__construct($DB, $id, $access_token, $secret, $proxy_url);
    }

    /**
     * Делаем запись на стене пользователя с указанным ID
     *
     * @param $user_id int ID владельца стены
     * @param $message string Сообщение
     * @param array $captcha
     * @return bool|mixed
     */
    public function postToWall($user_id, $message, $captcha=array())
    {
        $params = array(
            "owner_id" => $user_id,
            "message" => $message
        );

        return $this->send_request("wall.post", $params, true, $captcha);
    }

    /**
     * Подаем заявку или одобрям заявку на добавление в друзья пользователя с указанным ID
     *
     * @param $user_id
     * @param $text
     * @return mixed
     */
    public function friendsAdd($user_id, $text)
    {
        $params = array(
            "user_id" => $user_id,
            "text" => $text
        );

        return $this->send_request("friends.add", $params, true);
    }

    /**
     * Список городов по ID страны и региона
     *
     * @param $country_id
     * @param $region_id
     * @return mixed
     */
    public function databaseGetCities($country_id, $region_id)
    {
        $params = array(
            "country_id" => $country_id, // страна
            "region_id" => $region_id, // регион
            "need_all" => 0, // основные города
        );

        return $this->send_request("database.getCities", $params, true);
    }

    /**
     * Поиск пользователей Вконтакте по параметрам
     *
     * @param int $offset
     * @param int $count
     * @param int $age_from
     * @param int $sex
     * @param int $online
     * @return mixed
     */
    public function usersSearch($offset=0, $count=5, $age_from=17, $sex=0, $online=1)
    {
        // выбираем случайный интерес из массива
        $arIntereses = array('none','книги','общение','спорт','игры','музыка','фильмы','авто','живопись','искусство','путешествия','секс','недвижимость','деньги','шоппинг','интернет','бизнес','программирование');
        $randKey = array_rand($arIntereses);

        // параметры для фильтра
        $params = array(
            "offset" => $offset, // смещение
            "count" => $count, // количество записей
            "country" => 1, // страна (1 - Россия)
            //"city" => 1, // город (1 - Москва)
            "age_from" => $age_from, // основные города
            "online" => $online, // 1 — только в сети, 0 — все пользователи
            "sex" => $sex, // 1 — женщина, 2 — мужчина, 0 (по умолчанию) — любой
            "has_photo" => 1, // только с фото
            "status" => (rand(1,7)), // выбираем произвольно статусы
            "fields" => "sex", // мужчина или женщина (запоминаем в таблице)
        );
        if($arIntereses[$randKey]!='none')
        {
            $params["interests"] = $arIntereses[$randKey]; // случайный интерес (из популярных)
        }

        return $this->send_request("users.search", $params, true);
    }

    /**
     * Получаем последнюю запись со стены пользователя
     *
     * @param $owner_id
     * @param string $post
     * @return bool|mixed
     */
    public function wallGet($owner_id, $post="owner")
    {
        $params = array(
            "owner_id" => $owner_id, // ID пользователя, со стены кот. получаем запись
            "count" => 1, // берем последную запись
            "filter" => $post, // owner - только запись, которую сделал сам пользователь
        );

        return $this->send_request("wall.get", $params, true);
    }

    /**
     * Лайкаем объект пользователя
     *
     * @param $owner_id
     * @param $item_id
     * @return bool|mixed
     */
    public function likesAdd($owner_id, $item_id)
    {
        //'неплохо','круто','отлично', 'чудно', 'прекрасно', 'офигенно', 'здорово', 'отменно', 'нехило', 'класс', 'это пять', 'клево', 'гуд', 'бесподобно', 'славно', 'божественно'
        $params = array(
            "owner_id" => $owner_id, // ID владельца Like объекта
            "type" => "post", // идентификатор типа Like-объекта (post - стена пользователя или группы)
            "item_id" => $item_id, // идентификатор Like-объекта
        );

        return $this->send_request("likes.add", $params, true);
    }

    /**
     * Получаем статистику (счетчики) пользователя
     *
     * @param $uids
     * @return bool|mixed
     */
    public function usersGet($uids)
    {
        $params = array(
            "user_ids" => $uids, // перечисленные через запятую ID пользователей
            "fields" => "counters", // возвращаем только счетчики количества
        );

        return $this->send_request("users.get", $params, true);
    }

    /**
     * Получаем список всех групп текущего пользователя
     *
     * @param $user_id
     * @return bool|mixed
     */
    public function groupsGetOwner($user_id)
    {
        $params = array(
            "user_id" => $user_id, // идентификатор пользователя
        );
        return $this->send_request("groups.get", $params, true);
    }

    /**
     * Делаем репост записи
     *
     * @param $object_id
     * @return bool|mixed
     */
    public function wallRepost($object_id)
    {
        $params = array(
            "object" => $object_id, // строковый идентификатор объекта, например, wall66748_3675 или wall-1_340364
            //"message" => $message, // сопроводительный текст
            //"group_id" => $group_id, // идентификатор сообщества, на стене которого будет размещена запись
        );

        return $this->send_request("wall.repost", $params, true);
    }

    /**
     * Получаем подписчиков пользователя
     *
     * @param $user_id
     * @param int $count
     * @return bool|mixed
     */
    public function usersGetFollowers($user_id, $count=100)
    {
        $params = array(
            "user_id" => $user_id, // идентификатор пользователя
            "count" => $count, // количество подписчиков, информацию о которых нужно получить
        );

        return $this->send_request("users.getFollowers", $params, true);
    }

    /**
     * Получаем все заявки в друзья, которые мы отправили (на кого мы подписались)
     *
     * @param int $count
     * @return bool|mixed
     */
    public function userGetFriendRequests($count=1000)
    {
        $params = array(
            "count" => $count, // максимальное количество заявок на добавление в друзья, которые необходимо получить
            "out" => 1,
        );

        return $this->send_request("friends.getRequests", $params, true);
    }

    /**
     * Удаляет пользователя из списка друзей или убирает себя из его подписки
     *
     * @param $user_id
     * @return bool|mixed
     */
    public function userFriendsDelete($user_id)
    {
        $params = array(
            "user_id" => $user_id, // идентификатор пользователя, которого необходимо удалить из списка друзей, либо заявку от которого необходимо отклонить
        );

        return $this->send_request("friends.delete", $params, true);
    }

    /**
     * Удаляет выбранную запись со стены выбранного пользователя
     *
     * @param $owner_id
     * @param $post_id
     * @return bool|mixed
     */
    public function userWallDelete($owner_id, $post_id)
    {
        $params = array(
            //"owner_id" => $owner_id, // идентификатор пользователя или сообщества, на стене которого находится запись
            "post_id" => $post_id, // идентификатор записи на стене
        );

        return $this->send_request("wall.delete", $params, true);
    }

    /**
     * Сохраняем картинку капчи по ее URL и возвращаем к ней путь
     *
     * @param $url
     * @return string
     */
    public function getCaptchaImage($url)
    {
        $_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/ext_www/sc.bount.ru";
        $captchaPath = $_SERVER["DOCUMENT_ROOT"] . '/captcha_images/captcha.jpg';
        if (!file_exists($captchaPath)) unlink($captchaPath); // удаляем старый файл если он есть

        $ch = curl_init($url);
        $fp = fopen($captchaPath, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return $captchaPath;
    }

    /**
     * Получаем ошибку, вызванную запросом
     *
     * @return string
     */
    public function getError()
    {
        return $this->LAST_ERROR;
    }
}