<?php
return array (
	'class' => 'CDbConnection',
	'connectionString' => 'mysql:host=localhost;port=3306;dbname=',
	'username' => '',
	'password' => '',
	'emulatePrepare' => true,
	'charset' => 'utf8',
	'enableParamLogging' => true,
	'enableProfiling' => true,
	'schemaCachingDuration' => 3600,
	'tablePrefix' => 'uri_',
    // включаем профайлер
    'enableProfiling'=>true,
    // показываем значения параметров
    'enableParamLogging' => true,
);