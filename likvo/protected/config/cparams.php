<?php return array(
    // mail settings
    'emailFrom'=>'',
    'emailTo'=>'',
    'fromNameEmail'=>'',
    // check params settings
    'iscy'=>true, // true - повторная проверка CY и GLUE CY
    'ispr'=>true, // true - повторная проверка PR и GLUE PR
    'isdmoz'=>false, // true - проверка в DMOZ
    'isdmoz2'=>false, // true - проверка в DMOZ (проверка на втором шаге)
    'iswa'=>false, // true - проверка в Web Archive
    'iswa2'=>true, // true - проверка в Web Archive (проверка на втором шаге)
    'minCy'=>10, // минимальный тиц
    'minPr'=>1, // минимальный pr
    'cyPr'=>true, // тиц и pr вместе (true - AND или false - OR)
    // domain registration settings
    'tryRegMin'=>20, // в течение какого времени пытаться зарегистрировать домены
    'sleepSec'=>15, // интервал попыток регистрации (в секундах)
    'proxyapi'=>'http://hideme.ru/api/proxylist.php?out=js&maxtime=300&ports=80,8080&type=h&anon=1&code=#', // api url к списку проксей
);