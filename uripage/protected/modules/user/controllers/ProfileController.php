<?php

class ProfileController extends Controller
{
    /**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
            array('allow',   // allow ADMIN users to perform 'index' actions
                'actions'=>array('index'),
                'roles'=>array('ADMIN'),
            ),
            array('allow',  // allow auth users to perform 'index' actions
				'actions'=>array('settings'),
				'users'=>array('@'),
			),
			array('deny',    // deny all users
				'users'=>array('*'),
			),
		);
	}
    
    /**
	 * Displays the index page
	 */
    public function actionIndex()
    {
        // переменные и инициализация
        $domain = '';
        $error = '';
        $profile = '';
        $countries = '';

        // устанавливаем заголовок страницы
        $this->pageTitle=Yii::t('UserModule.user','Profile').' / '.Yii::app()->name;

        $cs = Yii::app()->clientScript;
        $cs->registerCssFile(Yii::app()->theme->baseUrl . '/web/css/datepicker.css');
        $cs->registerCoreScript('jquery');
        $cs->registerCoreScript('bootstrap');
        $cs->registerCoreScript('datepicker');

        // Повторная проверка WHOIS на 2-ом шаге, при передачи в качестве $_GET имени домена
        if(isset($_GET['name']))
        {
            $wd = new Whois( Yii::app()->getRequest()->getQuery('name') );
            if ($wd->isValid())
            {
                $namedata = $wd->getDomainsData();
                if(!$namedata[0]['errormessage'])
                {
                    if ($namedata[0]['isavailable'])
                    {
                        $domain = CHtml::encode($namedata[0]['domain']);
                        $profile = $namedata[0]['profile'];
                        $tldname = $namedata[0]['tldname'];
                        $price = $namedata[0]['price'];

                        // проверяем на возможность регистрации (по разрешенному списку из конфига)
                        if (!in_array($tldname,Yii::app()->params['allowBuyDomains']))
                        {
                            $dzone = '';
                            foreach (Yii::app()->params['allowBuyDomains'] as $domzone)
                            {
                                $dzone .= $domzone.', ';
                            }
                            if ($dzone) $dzone = substr($dzone, 0, strlen($dzone)-2);

                            $error = 'К сожалению, нельзя зарегистрировать выбранное имя в зоне \'<b>.'.strtoupper($tldname).'</b>\'!<br>Доступные зоны: <b>'.$dzone.'</b><br>Пожалуйста, <a href="/">выберите свободное имя в одной из этих зон</a>.';
                        }
                    }
                    else
                    {
                        $error = 'К сожалению, выбранное вами имя \'<b>'.$namedata[0]['domain'].'</b>\' уже занято!<br>Пожалуйста, попробуйте <a href="/">подобрать другое имя</a>.';
                    }
                }
                else
                {
                    $error = $namedata[0]['errormessage'];
                }
            }
            else
            {
                $error = $wd->getErrorText();
            }
        }
        else
        {
            $error = 'Для начала Вам необходимо <a href="/">выбрать имя</a>!';
        }

        if(!$error)
        {
            $model = new Domainprofiles($profile);

            // Ajax проверка формы
            if (isset($_POST['ajax']) && $_POST['ajax']==='profile-form')
            {
                echo CActiveForm::validate($model);
                Yii::app()->end();
            }

            $countries = Countries::model()->findAll(array('select'=>'iso,country'));

            // Если отправлен POST на сохранение введенных данных формы
            if(isset($_POST['Domainprofiles']))
            {
                // Старт транзакции
                $transaction = Yii::app()->db->beginTransaction();
                try
                {
                    $request = Yii::app()->request->getPost('Domainprofiles');

                    $model->created = new CDbExpression('NOW()');
                    $model->email = $request['email'];
                    $model->phone = $request['phone'];
                    $model->type = $profile;
                    $model->name = "Профиль от ".date("d.m.Y H:i:s");
                    $model->country = $request['country'];
                    $model->org_address = '';

                    // Если выбраны домены для физ лиц в ru/рф/su
                    if($profile === Whois::PROFILE_FZRU)
                    {
                        $model->ru_first_name = $request['ru_first_name'];
                        $model->ru_last_name = $request['ru_last_name'];
                        $model->ru_middle_name = $request['ru_middle_name'];
                        $model->birth_date = $request['birth_date'];
                        $model->en_first_name = UString::get_in_translate_to_en($model->ru_first_name);
                        $model->en_last_name = UString::get_in_translate_to_en($model->ru_last_name);
                        $model->en_middle_name = substr(UString::get_in_translate_to_en($model->ru_middle_name), 0, 1);
                        $model->pasport_num = $request['pasport_num'];
                        $model->pasport_iss = $request['pasport_iss'];
                        $model->pasport_date = $request['pasport_date'];
                    }
                    elseif($profile === Whois::PROFILE_URRU)
                    {
                        // данные для регистрации на юр.лиц
                        $model->org_ru_name = $request['org_ru_name'];
                        $model->org_inn = $request['org_inn'];
                        $model->org_kpp = $request['org_kpp'];
                        $model->org_address = $request['org_address'];
                    }
                    elseif($profile === Whois::PROFILE_INTR)
                    {
                        $model->en_first_name = $request['en_first_name'];
                        $model->en_last_name = $request['en_last_name'];
                        $model->org_name = $request['org_name'];
                    }

                    $model->pochta_code = $request['pochta_code'];
                    $model->pochta_region = $request['pochta_region'];
                    $model->pochta_city = $request['pochta_city'];
                    $model->pochta_address = $request['pochta_address'];
                    $model->pochta_to = $request['pochta_to'];

                    // данные для доменов xxx и pro
                    //$model->xxx_sponsored = $request['xxx_sponsored'];
                    //$model->pro_profession = $request['pro_profession'];
                    //$model->pro_license_number = $request['pro_license_number'];
                    //$model->pro_licensing_auth = $request['pro_licensing_auth'];
                    //$model->pro_auth_website = $request['pro_auth_website'];

                    // Если пользователь не зарегистрирован создаем ему новый профиль и отправляем уведомление на почту
                    if (Yii::app()->user->isGuest)
                    {
                        $model->isdefault = '1';

                        // Регистрируем нового пользователя
                        Yii::app()->getModule('user');
                        $new_passwd = UString::generate_password();
                        $userModel = new User('createUser');
                        $userModel->email = $request['email'];
                        $userModel->password = $new_passwd;
                        $userModel->code_word = UString::get_generate_word();
                        $userModel->ip = Yii::app()->request->userHostAddress;

                        // Создаем пользователя, если пользователь не создается то далее делается rollBack базы
                        if ($userModel->validate())
                        {
                            $userModel->save(false);
                            $model->user_id = $userModel->uid;
                        }
                    }
                    else
                    {
                        $model->user_id = Yii::app()->user->id;
                        // TODO: нужна проверка на наличие профилей по умолчанию для зарегистрированных
                    }

                    // Если пользователь создался, создаем новый профиль
                    if($model->user_id > 0)
                    {
                        if ($model->validate())
                        {
                            // Сохраняем профиль
                            $model->save(false);

                            $ordersModel = new Orders('domain');
                            $ordersModel->user_id = $model->user_id;
                            $ordersModel->domain = $domain;
                            //$ordersModel->punycode = '';
                            $ordersModel->operation = 'REG';
                            $ordersModel->sum = $price.'.00';
                            $ordersModel->currency = 'RUB';
                            $ordersModel->status = 'ORDER';
                            $ordersModel->period = 1;
                            $ordersModel->timestamp = new CDbExpression('NOW()');
                            $ordersModel->update_ts = new CDbExpression('NOW()');

                            if ($ordersModel->validate())
                            {
                                $ordersModel->save(false);
                                $transaction->commit();

                                // Отправляем уведомление о создании нового профиля на E-mail, если пользователь Гость
                                if (Yii::app()->user->isGuest)
                                {
                                    // получаем шаблони из базы и вставляем данные для отправки
                                    $mailtemplates = Mailtemplates::model()->find(array(
                                        'select'=>'data',
                                        'condition'=>'name=:name',
                                        'params'=>array(':name'=>'regstep'),
                                    ));
                                    $message = CHtml::decode($mailtemplates->data);
                                    $message = str_replace("{site_name}", Yii::t('main', 'siteurl'), $message);
                                    $message = str_replace("{domain_name}", $ordersModel->domain, $message);
                                    $message = str_replace("{email}", $model->email, $message);
                                    $message = str_replace("{passwd}", $new_passwd, $message);
                                    $message = str_replace("{secret_word}", $userModel->code_word, $message);
                                    $message = str_replace("{url_pay}", CHtml::link('оплатить', $this->createAbsoluteUrl('main/pay')), $message);

                                    //get template 'simple' from /themes/default/views/mail
                                    $mail = new YiiMailer('simple', array('message'=>$message, 'description'=>'Информационное сообщение'));
                                    //render HTML mail, layout is set from config file or with $mail->setLayout('layoutName')
                                    $mail->render();
                                    //set properties as usually with PHPMailer
                                    $mail->From = Yii::app()->params['adminEmail'];
                                    $mail->FromName = Yii::app()->params['fromNameEmail'];
                                    $mail->Subject = 'Ваша регистрационная информация';
                                    $mail->AddAddress($model->email);
                                    //send
                                    if ($mail->Send())
                                    {
                                        $mail->ClearAddresses();
                                    }
                                    else
                                    {
                                        //Yii::app()->user->setFlash('recoveryMessage',Yii::t('UserModule.user', 'Error while sending email: {error}', array('{error}' => $mail->ErrorInfo)));
                                        //echo $mail->ErrorInfo;
                                    }
                                }

                                // авторизация редирект на 3й шаг (оплаты)
                                // TODO: автоматическая авторизация под созданной учетной записью
                                $this->redirect(array('/payment/order','id'=>$ordersModel->id));
                            }
                            else
                            {
                                $transaction->rollBack(); // откат связанных данных, если не удалось сохранить данные
                            }
                        }
                        else
                        {
                            $transaction->rollBack(); // откат связанных данных, если не удалось сохранить данные
                            //VarDumper::dump($model->getErrors());
                        }
                    }
                    else
                    {
                        $transaction->rollBack(); // откат связанных данных, если не удалось сохранить данные
                        //VarDumper::dump($userModel->getErrors());
                    }
                }
                catch(Exception $e)
                {
                    // Откатываем сохранение данных
                    $transaction->rollback();
                }
            } // end if get POST
        }

        // Рендер отображения
        $this->render('index',array('model'=>$model,'domain'=>$domain,'profile'=>$profile,'countries'=>$countries,'error'=>$error));
    }

    /**
     * Change password
     */
    public function actionSettings()
    {
        $this->pageTitle=Yii::t('UserModule.user', 'Change Password').' / '.Yii::app()->name;
        Yii::app()->clientScript->registerPackage('bootstrap');

        $model = new ChangePassword;

        if (Yii::app()->user->id)
        {
            // ajax validator
            if(isset($_POST['ajax']) && $_POST['ajax']==='changepwd-form ')
            {
                echo UActiveForm::validate($model);
                Yii::app()->end();
            }

            if(isset($_POST['ChangePassword']))
            {
                $new_password = User::model()->findbyPk(Yii::app()->user->id);

                $model->attributes=$_POST['ChangePassword'];
                $model->code=$new_password->salt;
                $model->md5pwd=$new_password->password;

                if($model->validate())
                {
                    $new_password->salt=$new_password->generateSalt();
                    $new_password->password = md5($new_password->salt.$model->password);

                    if($new_password->save())
                    {
                        Yii::app()->user->setFlash('editMessage',Yii::t('UserModule.user', 'New password is saved'));
                        $this->redirect(array("/user/profile"));
                    }
                    else
                    {
                        $model->addError('oldPassword', Yii::t('UserModule.user', 'Unknow error. Please contact with us by E-mail: {admin_email}', array('{admin_email}'=>Yii::app()->params['adminEmail'])));
                        $this->render("changepassword", array('model2' => $model));
                    }
                }
            }
            $this->render('settings',array('model2'=>$model));
        }
    }
}