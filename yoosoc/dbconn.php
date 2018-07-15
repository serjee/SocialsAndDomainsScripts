<?php

/* Параметры подключения */
$arConn = array(
    'host'=>'localhost',
    'dbname'=>'',
    'dbuser'=>'',
    'dbpass'=>'',
);

/* Подключаемся к базе данных */
try
{
    $DB = new PDO('mysql:host='.$arConn['host'].';dbname='.$arConn['dbname'], $arConn['dbuser'], $arConn['dbpass'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
} catch (PDOException $e) {
    print "Ошибка подключения к базе данных!: " . $e->getMessage() . "<br/>";
    die();
}