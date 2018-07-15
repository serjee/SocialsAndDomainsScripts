<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/dbconn.php");

// Обработка добавления нового аккаунта
if(isset($_REQUEST["action"]) && $_REQUEST["action"]==="new_account")
{
    $strError = "";
    $strOk = false;
    if(empty($_REQUEST["phone"]) || !is_numeric($_REQUEST["phone"]) || strlen($_REQUEST["phone"])!=11) {
        $strError .= "- Телефон не заполнен или заполнен неверно.<br />";
    }
    if(isset($_REQUEST["user_id"]) && strlen($_REQUEST["user_id"])>50) {
        $strError .= "- ID пользователя не заполнен или заполнен неверно.<br />";
    }
    if(empty($_REQUEST["pass"]) || strlen($_REQUEST["pass"])>50) {
        $strError .= "- Пароль не заполнен или заполнен неверно.<br />";
    }
    if(isset($_REQUEST["access_token"]) && strlen($_REQUEST["access_token"])>255) {
        $strError .= "- Токен слишком большой.<br />";
    }
    if(isset($_REQUEST["secret"]) && strlen($_REQUEST["secret"])>255) {
        $strError .= "- Секрет слишком большой.<br />";
    }

    // Добавляем если нет ошибок
    if($strError=="")
    {
        $DB->query("INSERT INTO yoo_accounts (soc,sex,phone,user_id,pass,access_token,secret,status) VALUE('".$_REQUEST["soc"]."', '".$_REQUEST["sex"]."', '".$_REQUEST["phone"]."', '".$_REQUEST["user_id"]."', '".$_REQUEST["pass"]."', '".$_REQUEST["access_token"]."', '".$_REQUEST["secret"]."', '".$_REQUEST["status"]."')");
        $strOk = true;
    }
}

// Вытаскиваем все аккаунты из базы и производим нужные действия
$arAccounts = array();
foreach ($DB->query("SELECT user_id, phone, soc, sex, count_friends, count_followers, status FROM yoo_accounts WHERE status IN ('ACTIVE','UNDERWAY','SALE','DISABLED','TEST','GROUP')") as $rowAcc)
{
    $arAccounts[] = $rowAcc;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <title>Обновление электронных изданий</title>
    <!-- Bootstrap core CSS -->
    <link href="/theme/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<!-- Begin page content -->
<div class="container">

    <div class="row">
        <div class="col-md-4">
            <div class="page-header">
                <h1>Новый аккаунт</h1>
            </div>
            <?php
            if ($strError!="") {
                echo '<p class="bg-danger">'.$strError.'</p>';
            }
            if ($strOk) {
                echo '<p class="bg-success">Аккаунт успешно добавлен.</p>';
            }
            ?>
            <form role="form" method="post">
                <div class="form-group">
                    <label style="color:red" for="exampleInputEmail1">Соц.сеть <span class="required">*</span></label>
                    <select class="form-control" name="soc">
                        <option value="VK">Вконтакте</option>
                        <option value="OK">Однокласники</option>
                        <option value="FB">Фейсбук</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="color:red" for="exampleInputEmail1">Пол <span class="required">*</span></label>
                    <select class="form-control" name="sex">
                        <option value="MALE">Мужчина</option>
                        <option value="FEMALE">Женщина</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="color:red" for="exampleInputEmail1">Логин/Телефон <span class="required">*</span></label>
                    <input type="text" name="phone" class="form-control" id="exampleInputEmail1" placeholder="Пример: 79031234567">
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">ID пользователя</label>
                    <input type="text" name="user_id" class="form-control" id="exampleInputEmail1" placeholder="Пример: 1234567">
                </div>
                <div class="form-group">
                    <label style="color:red" for="exampleInputPassword1">Пароль <span class="required">*</span></label>
                    <input type="text" name="pass" class="form-control" id="exampleInputPassword1">
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Токен</label>
                    <input type="text" name="access_token" class="form-control" id="exampleInputEmail1">
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Секрет</label>
                    <input type="text" name="secret" class="form-control" id="exampleInputEmail1">
                </div>
                <div class="form-group">
                    <label style="color:red" for="exampleInputEmail1">Статус <span class="required">*</span></label>
                    <select class="form-control" name="status">
                        <option value="ACTIVE">Активен</option>
                        <option value="UNDERWAY">В работе</option>
                        <option value="SALE">В продаже</option>
                        <option value="SOLD">Продан</option>
                        <option value="TEST">Тестовый</option>
                        <option value="GROUP">Владелец группы</option>
                        <option value="DISABLED">Отключен</option>
                        <option value="BLOCKED">Заблокирован</option>
                    </select>
                </div>
                <input type="hidden" name="action" value="new_account">
                <button type="submit" class="btn btn-lg btn-success">Создать</button>
            </form>
        </div>
        <div class="col-md-8">
            <div class="page-header">
                <h1>Список аккаунтов</h1>
            </div>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Логин</th>
                    <th>Сеть</th>
                    <th>Пол</th>
                    <th>Друзей</th>
                    <th>Подпис.</th>
                    <th>Статус</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if(count($arAccounts)>0)
                {
                    foreach($arAccounts as $account)
                    {
                        echo "<tr>";
                        if($account["user_id"]>0) {
                            echo "<td><a href=\"https://vk.com/id".$account["user_id"]."\" target=\"_blank\">".$account["phone"]."</a></td>";
                        } else {
                            echo "<td>".$account["phone"]."</td>";
                        }
                        echo "<td>".$account["soc"]."</td>";
                        echo "<td>".$account["sex"]."</td>";
                        echo "<td>".$account["count_friends"]."</td>";
                        echo "<td>".$account["count_followers"]."</td>";
                        echo "<td>".$account["status"]."</td>";
                        echo "</tr>";
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<br />
</body>
</html>