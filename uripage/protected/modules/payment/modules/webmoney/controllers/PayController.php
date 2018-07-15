<?php

class PayController extends Controller
{

	public $defaultAction = 'order';

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
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('order','result','success','fail'),
				'users'=>array('*'),
			),
            /*
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('update'),
                'roles'=>array('ADMIN'),
			),
            */
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    /**
     * Output pay form (for more detale see WmPayFormWidget)
     */
    public function actionOrder()
    {
        if (Yii::app()->user->isGuest)
        {
            Yii::app()->user->setFlash('error', "<strong>Ошибка!</strong> Чтобы произвести платеж, Вам нужно <a href='/user/account/login/'>авторизироваться на сайте</a>!");
            $this->render('create');
        }
        else
        {
            $orderId = intval(Yii::app()->request->getQuery('id'));

            if ($orderId > 0)
            {
                $order = Orders::model()->findByPk($orderId);

                if ($order === null)
                {
                    Yii::app()->user->setFlash('error', "<strong>Ошибка!</strong> Заказа с таким номером не найдено в базе!");
                }
                else
                {
                    // Пытаемся найти транзакцию по номеру заказа
                    $payment = Logbalance::model()->count('order_id=:order_id',array(':order_id'=>$orderId));

                    // Если не нашли транзакцию, создаем новую
                    if(!$payment)
                    {
                        $payment = new Logbalance;
                        $payment->user_id = Yii::app()->user->id;
                        $payment->order_id = $orderId;
                        $payment->amount = $order['sum'].'.00';
                        $payment->in_out = 'IN';
                        $payment->pay_system = 'WMR';
                        $payment->payed_type = 'MAN';
                        $payment->state = 'I';
                        $payment->timestamp = new CDbExpression('NOW()');
                        $payment->save();
                    }
                }
            }
            else
            {
                Yii::app()->user->setFlash('error', "<strong>Ошибка!</strong> Введен неверный номер заказа!");
            }

            $this->render('create',array('order'=>$order,));
        }
    }

    /**
     * Result action - create model WmForm, validate it and run success order method
     */
    public function actionResult()
    {
        $request = Yii::app()->request;

        $wmForm = new WmForm();
        $wmForm->LMI_PAYEE_PURSE = $request->getPost('LMI_PAYEE_PURSE');
        $wmForm->LMI_PAYMENT_AMOUNT = $request->getPost('LMI_PAYMENT_AMOUNT');
        $wmForm->LMI_PAYMENT_NO = $request->getPost('LMI_PAYMENT_NO');
        $wmForm->LMI_MODE = $request->getPost('LMI_MODE');
        $wmForm->LMI_PAYER_PURSE = $request->getPost('LMI_PAYER_PURSE');
        $wmForm->LMI_PAYER_WM = $request->getPost('LMI_PAYER_WM');

        // Обработка пререквеста и выполнение платежа
        if($request->getPost('LMI_PREREQUEST') == 1)
        {
            try
            {
                if($wmForm->validate())
                {
                    // Вытаскиваем параметры платежа
                    $payment = Logbalance::model()->find('order_id=:order_id AND state=:state', array(':order_id'=>$wmForm->LMI_PAYMENT_NO,':state'=>'I'));
                    if ($payment !== null)
                    {
                        // Обновляем статус платежа
                        $payment->state = 'P';
                        $payment->lmi_payer_purse = $wmForm->LMI_PAYER_PURSE;
                        $payment->lmi_payer_wm = $wmForm->LMI_PAYER_WM;
                        $payment->save();
                        exit('YES');
                    }
                    else
                    {
                        exit('Заказ с данным ID не найден в базе данных.');
                    }
                }
                else
                {
                    exit(CHtml::errorSummary($wmForm));
                }
            }
            catch (Exception $e)
            {
                exit($e->getMessage());
            }
        }
        else
        {
            try
            {
                $wmForm->scenario = 'paydone';

                $wmForm->LMI_SYS_INVS_NO = $request->getPost('LMI_SYS_INVS_NO');
                $wmForm->LMI_SYS_TRANS_NO = $request->getPost('LMI_SYS_TRANS_NO');
                $wmForm->LMI_SYS_TRANS_DATE = $request->getPost('LMI_SYS_TRANS_DATE');
                $wmForm->LMI_SECRET_KEY = $request->getPost('LMI_SECRET_KEY');
                $wmForm->LMI_HASH = $request->getPost('LMI_HASH');

                if($wmForm->validate())
                {
                    // Вытаскиваем параметры платежа
                    $payment = Logbalance::model()->find('order_id=:order_id AND state=:state', array(':order_id'=>$wmForm->LMI_PAYMENT_NO,':state'=>'P'));
                    if ($payment !== null)
                    {
                        // Обновляем статус платежа
                        $payment->state = 'R';
                        $payment->lmi_sys_invs_no = $wmForm->LMI_SYS_INVS_NO;
                        $payment->lmi_sys_trans_no = $wmForm->LMI_SYS_TRANS_NO;
                        $payment->lmi_sys_trans_date = $wmForm->LMI_SYS_TRANS_DATE;
                        $payment->save();
                        exit('YES');
                    }
                    else
                    {
                        exit('Заказ с данным ID не найден в базе данных.');
                    }
                }
                else
                {
                    exit(CHtml::errorSummary($wmForm));
                }
            }
            catch (Exception $e)
            {
                exit($e->getMessage());
            }
        }
    }

	/**
	* Success message
	*/
	public function actionSuccess()
    {
        $this->setPayStatus(true);

        Yii::app()->user->setFlash('success', "Вы успешно оплатили Ваш заказ №".intval(Yii::app()->request->getPost('LMI_PAYMENT_NO')));
        $this->render('message');
	}

	/**
	* Fail message
	*/
	public function actionFail()
    {
        $this->setPayStatus(false);

        Yii::app()->user->setFlash('error', "Во время платежа произошла ошибка, пожалуйста повторите платеж!");
        $this->render('message');
	}

    /**
     * Устанавливает статус платежа в базе данных
     * @param bool $success
     */
    private function  setPayStatus($success = false)
    {
        $request = Yii::app()->request;

        // Вытаскиваем параметры платежа
        $criteria = new CDbCriteria;
        $criteria->condition='order_id=:order_id AND state=:state AND lmi_sys_invs_no=:lmi_sys_invs_no AND lmi_sys_trans_no=:lmi_sys_trans_no';
        $criteria->params=array(
            ':order_id' => $request->getPost('LMI_PAYMENT_NO'),
            ':state' => 'R',
            ':lmi_sys_invs_no' => $request->getPost('LMI_SYS_INVS_NO'),
            ':lmi_sys_trans_no' => $request->getPost('LMI_SYS_TRANS_NO')
        );
        $payment = Logbalance::model()->find($criteria);

        if ($payment !== null)
        {
            if ($success)
            {
                $order = Orders::model()->findByPk(intval($request->getPost('LMI_PAYMENT_NO')));

                // Заказ на регистрацию и продление домена
                if ($order->operation === 'REG' || $order->operation === 'REREG')
                {
                    //TODO: Оформление домена на регистрационные данные пользователя
                    $order->status = 'DONE';
                    $order->save();
                    $payment->state = 'S';
                    $payment->save();
                }
                elseif ($order->operation === 'BALANCE') // Заказ на пополнение баланса
                {
                    //Пополнение баланса
                    $user = User::model()->findByPk(Yii::app()->user->id);
                    $user->balance = $user->balance + $order->sum;
                    if ($user->save())
                    {
                        $order->status = 'DONE';
                        $order->save();
                        $payment->state = 'S';
                        $payment->save();
                    }
                }
                else
                {
                    $order->status = 'DONE';
                    $order->save();
                    $payment->state = 'S';
                    $payment->save();
                }
            }
            else
            {
                $payment->state = 'F';
                $payment->save();
            }
        }
    }
}
