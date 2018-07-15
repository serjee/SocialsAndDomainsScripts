<?php
$params = array(
    // настройки почты
    'emailFrom'=>'',
    'emailTo'=>'',
    'fromNameEmail'=>'',

    // настройки задержек
    'tryRegMin'=>20, // в течение какого времени пытаться зарегистрировать домены
    'sleepSec'=>15, // интервал попыток регистрации (в секундах)

    // url api доступа к прокси
    'proxyapi'=>'http://hideme.ru/api/proxylist.txt?out=js&country=RU&maxtime=999&type=s&code=', // api url к списку проксей
 
    // ключ AntiGate.com
    'antigate_key'=>'',

    // Сообщения для различных действий
    'textMaleAddFriend'=>'Привет, добавь меня в друзья пожалуйста.', // мужской текст при добавлении в друзья
    'textFemaleAddFriend'=>'Приветик, давай дружить?', // женский текст при добавлении в друзья
);