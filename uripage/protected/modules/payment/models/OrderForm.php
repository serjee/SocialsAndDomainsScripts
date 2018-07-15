<?php

class OrderForm extends CFormModel
{
    /**
     * Констанды платежных систем, доступных для выбора пользователем
     */
    const WMR = 'WMR';
    const WMZ = 'WMZ';
    const YAM = 'YAM';
    const QIWI = 'QIWI';
    const PAYPAL = 'PAYPAL';
    const LC = 'LC';
    const NAL = 'NAL';
    const PIN = 'PIN';

    /**
     * Выбранная пользователем платежная система
     * @var string
     */
    public $paysys;
    /**
     * Сумма платежа
     * @var int
     */
    public $amount;
    /**
     * Номер ордера
     * @var int
     */
    public $order_id;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('paysys', 'required'),
            array('amount', 'required', 'on'=>'balance', 'message'=>'Укажите пожалуйста сумму, на которую необходимо пополнить баланс!'),
            array('order_id', 'numerical', 'integerOnly'=>true),
            array('amount', 'numerical'),
            array('paysys', 'match','pattern'=>'/(WMR|WMZ|YAM|QIWI|PAYPAL|LC|NAL|PIN)/i','message'=>'Выбранна неверная платежная система'),
            array('order_id', 'validateOrder'),
            array('amount', 'validateAmount', 'on'=>'balance'),
        );
    }

    /**
     * Валидатор для проверки существования номера заказа
     * @return bool
     */
    public function validateOrder()
    {
        if (!empty($this->order_id))
        {
            $order = Orders::model()->findByPk($this->order_id);
            if ($order === null)
            {
                $this->addError('order_id', 'Заказа с таким ID не существует');
                return false;
            }
        }
    }

    /**
     * Валидатор для проверки существования номера заказа
     * @return bool
     */
    public function validateAmount()
    {
        if ($this->paysys === 'WMR')
        {
            if ($this->amount > 500000)
            {
                $this->addError('amount', 'Сумма платежа для WebMoney не может превышать 500 000 рублей.');
                return false;
            }
        }
        elseif ($this->paysys === 'YAM')
        {
            if ($this->amount > 15000)
            {
                $this->addError('amount', 'Сумма платежа для Яндекс.Денег не может превышать 15 000 рублей.');
                return false;
            }
        }
        elseif ($this->paysys === 'QIWI')
        {
            if ($this->amount > 15000)
            {
                $this->addError('amount', 'Сумма платежа для QIWI-кошелька не может превышать 15 000 рублей.');
                return false;
            }
        }
        else
        {
            if ($this->amount > 500000)
            {
                $this->addError('amount', 'Сумма платежа не может превышать 500 000 рублей.');
                return false;
            }
        }
    }
}