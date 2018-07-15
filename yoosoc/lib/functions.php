<?php

/**
 * Сообщение при Добавлении в друзья (генерация умного сообщения)
 *
 * @param $userFromSex
 * @param $userToSex
 * @param $userToName
 * @return string
 */
function getMessageWhileFriendAdd($userFromSex, $userToSex, $userToName)
{
    // Массивы для генерации сообщений
    $arDataHello = array('Привет,','Приветик','Здравствуй,','Как дела?','Приветствую,','Доброго,');
    $arDataGive = array('Давай дружить?','Добавь меня в друзья,','Добавляйся ко мне в друзья,','Хочешь со мной дружить?','Будешь со мной дружить?','Давай станем друзьями?','Хочешь дружить?','Будем дружить?','Станешь со мной дружить?');
    $arDataPlease = array('пожалуйста','плиз');
    // От мужчин, женщинам
    $arDataPrilF = array('самая','очень','довольно','несказанно','такая','фантастически','бесподобно');
    $arDataKompF = array('красивая','привлекательная','милая','очаровательная','обворожительная','неповторимая','незабываемая','неотразимая','шикарная','ослепительная','недоступная','божественная','завораживающая','ангельская','лучезарная','яркая','обалденная','сногсшибательная','обольстительная','сказочная','симпатичная','умопомрачительная','желанная','непредсказуемая','загадочная','цветущая','безупречная','лучшая','изысканная','соблазнительная','прелестная','обаятельная','бесподобная','лучезарная','ненаглядная','изумительная','сказочная');
    $arDataEndF = array('девушка','прелесть','женщина','красотка');
    // От женщин, мужчинам
    $arDataPrilM = array('самый','очень','довольно','необычайно','такой');
    $arDataKompM = array('безупречный','бесподобный','дружелюбный','желанный','жизнерадостный','загадочный','зажигательный','изумительный','изысканный','кокетливый','красивый','лучший','любвеобильный','манящий','мечтательный','милый','мужественный','недоступный','неотразимый','неповторимый','обалденный','обаятельный','обворожительный','обольстительный','отпадный','очаровательный','привлекательный','романтичный','сексуальный','сказочный','сладенький','сногсшибательный','соблазнительный','стильный','яркий');
    $arDataEndМ = array('парень','мужчина');
    // От мужчин, мужчинам
    $arDataM = array("Друг","Приятель","Братуха","Чел","Мен");
    // От женщин, женщинам
    $arDataF = array("Подруга","Слушай","Систер","Зай","Дорогая");

    $messageText = "";

    // генерация общих данных
    $randKeyHello = array_rand($arDataHello);
    $randKeyGive = array_rand($arDataGive);
    $randKeyPlease = array_rand($arDataPlease);

    // определения типа сообщения и генерация
    if($userFromSex=="MALE" && $userToSex=="F") // от мужчины, женщине
    {
        // генерация данных
        $randKeyPrilF = array_rand($arDataPrilF);
        $randKeyKompF = array_rand($arDataKompF);
        $randKeyEndF = array_rand($arDataEndF);

        // составляем сообщение
        $messageText = $arDataHello[$randKeyHello]." ".$userToName."! Ты ".$arDataPrilF[$randKeyPrilF]." ".$arDataKompF[$randKeyKompF]." ".$arDataEndF[$randKeyEndF].". ".$arDataGive[$randKeyGive]." ".$arDataPlease[$randKeyPlease].".";
    }
    elseif($userFromSex=="FEMALE" && $userToSex=="M") // от женщины, мужчине
    {
        // генерация данных
        $randKeyPrilM = array_rand($arDataPrilM);
        $randKeyKompM = array_rand($arDataKompM);
        $randKeyEndМ = array_rand($arDataEndМ);

        // составляем сообщение
        $messageText = $arDataHello[$randKeyHello]." ".$userToName."! Ты ".$arDataPrilM[$randKeyPrilM]." ".$arDataKompM[$randKeyKompM]." ".$arDataEndМ[$randKeyEndМ].". ".$arDataGive[$randKeyGive]." ".$arDataPlease[$randKeyPlease].".";
    }
    elseif($userFromSex=="FEMALE" && $userToSex=="F") // от женщине, женщине
    {
        // генерация данных
        $randKeyF = array_rand($arDataF);
        $randKeyKompF = array_rand($arDataKompF);

        // составляем сообщение
        $messageText = $arDataHello[$randKeyHello]." ".$userToName."! Ты ".$arDataKompF[$randKeyKompF].". ".$arDataF[$randKeyF].", ".$arDataGive[$randKeyGive]." ".$arDataPlease[$randKeyPlease].".";
    }
    elseif($userFromSex=="MALE" && $userToSex=="M") // от мужчины, мужчине
    {
        // генерация данных
        $randKeyM = array_rand($arDataM);

        // составляем сообщение
        $messageText = $arDataHello[$randKeyHello]." ".$userToName."! ".$arDataM[$randKeyM].", ".$arDataGive[$randKeyGive]." ".$arDataPlease[$randKeyPlease].".";
    }
    else // остальные
    {
        // составляем сообщение
        $messageText = $arDataHello[$randKeyHello]." ".$userToName."! ".$arDataGive[$randKeyGive]." ".$arDataPlease[$randKeyPlease].".";
    }

    return $messageText;
}