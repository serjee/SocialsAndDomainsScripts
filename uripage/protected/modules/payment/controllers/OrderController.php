<?php

class OrderController extends Controller
{
	public function actionIndex()
	{
        // устанавливаем заголовок страницы
        $this->pageTitle = Yii::app()->name.' - '.Yii::t('main', 'Project description');
        Yii::app()->clientScript->registerCoreScript('jquery');
        Yii::app()->clientScript->registerCoreScript('bootstrap');

        // Если вошел гость, то выводим ошибку
        if (Yii::app()->user->isGuest)
        {
            Yii::app()->user->setFlash('error','Чтобы оплатить заказ или пополнить баланс, пожалуйста, <a class="dotted" href="/user/account/login/">авторизируйтесь на сайте</a>!');
            $this->render('index');
        }
        else
        {
            $orderId = intval(Yii::app()->request->getQuery('id'));
            $order = Orders::model()->findByPk($orderId);

            $model = new OrderForm();

            // Устанавливаем сценарий в случае пополнения баланса, где требуется дополнительная проверка ввода поля суммы платежа
            if ($orderId < 1)
            {
                $model->scenario = 'balance';
            }

            // collect user input data
            if(isset($_POST['OrderForm']))
            {
                $request = Yii::app()->request->getPost('OrderForm');
                $model->order_id = $request['order_id'];
                $model->amount = $request['amount'];
                $model->paysys = $request['paysys'];

                if($model->validate())
                {
                    // Создаем новый заказ если он не создан и редиректим пользователя на страницу оплаты
                    $order = new Orders();
                    $order->user_id = Yii::app()->user->id;
                    $order->operation = 'BALANCE';
                    $order->sum = $request['amount'].'.00';
                    $order->currency = 'RUB';
                    $order->status = 'ORDER';
                    $order->timestamp = new CDbExpression('NOW()');
                    $order->update_ts = new CDbExpression('NOW()');

                    if ($order->validate())
                    {
                        $order->save(false);
                        // Редирект на модуль платежной системы для оплаты
                        switch($request['paysys'])
                        {
                            case "WMR":
                                $this->redirect(array('/payment/webmoney/pay/order','id'=>$order->id));
                                break;
                            case "QIWI":
                                $this->redirect(array('/payment/qiwi/pay/order','id'=>$order->id));
                                break;
                            case "YAM":
                                $this->redirect(array('/payment/yandex/pay/order','id'=>$order->id));
                                break;
                            case "PAYPAL":
                                $this->redirect(array('/payment/paypal/pay/order','id'=>$order->id));
                                break;
                            case "PIN":
                                $this->redirect(array('/payment/pin/pay/order','id'=>$order->id));
                                break;
                            case "LC":
                                $this->redirect(array('/payment/lic/pay/order','id'=>$order->id));
                                break;
                        }
                    }
                }
            }

            // Рендер отображения
            $this->render('index', array(
                'model'=>$model,
                'orderId'=>$orderId,
                'orderSum'=>floatval($order->sum),
            ));
        }
	}
}