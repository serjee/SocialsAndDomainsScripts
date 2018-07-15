<?php

class WmForm extends CFormModel
{
    /**
     * Кошелек продавца
     * @var string
     */
    public $LMI_PAYEE_PURSE;
    /**
     * Сумма, которую заплатил покупатель. Дробная часть отделяется точкой.
     * @var float
     */
    public $LMI_PAYMENT_AMOUNT;
    /**
     * Номер покупки в соответствии с системой учета продавца
     * @var int
     */
    public $LMI_PAYMENT_NO;
    /**
     * Указывает, в каком режиме выполнялась обработка запроса на платеж:
     * 0 - Платеж был реален, деньги с кошелька списались
     * 1 - Платеж выполнялся в тестовом режиме
     * @var int
     */
    public $LMI_MODE;
    /**
     * Номер счета в системе WebMoney Transfer, выставленный покупателю
     * @var string
     */
    public $LMI_SYS_INVS_NO;
    /**
     * Номер платежа в системе WebMoney Transfer,
     * выполненный в процессе обработки запроса на выполнение платежа сервисом Web Merchant Interface.
     * @var string
     */
    public $LMI_SYS_TRANS_NO;
    /**
     * Дата и время реального прохождения платежа в формате “YYYYMMDD HH:MM:SS”.
     * @var string
     */
    public $LMI_SYS_TRANS_DATE;
    /**
     * Secret Key
     * @var string
     */
    public $LMI_SECRET_KEY;
    /**
     * Кошелек, с которого покупатель совершил платеж.
     * @var string
     */
    public $LMI_PAYER_PURSE;
    /**
     * WM-идентификатор покупателя, совершившего платеж.
     * @var string
     */
    public $LMI_PAYER_WM;
    /**
     * Контрольная подпись оповещения о выполнении платежа
     * @var string
     */
    public $LMI_HASH;
    /**
     * Опции платежной системы
     * @var array
     */
	private $_options;

	/**
	 * Declares the validation rules.
	 */
	public function rules()
    {
		return array(
			array('LMI_PAYMENT_NO,LMI_PAYER_WM,LMI_MODE', 'numerical', 'integerOnly'=>true),
			array('LMI_PAYMENT_AMOUNT', 'numerical'),
            array('LMI_PAYER_WM', 'match','pattern'=>'/\d{12}/i','message'=>Yii::t('models','WMID должен иметь 12 значный номер')),
			array('LMI_PAYEE_PURSE,LMI_PAYER_PURSE', 'match','pattern'=>'/[z,u,r]\d{12}/i','message'=>'Кошелек должен содержать 1 букву и 12 значный номер'),
            array('LMI_PAYMENT_NO', 'validatePaymentNo'),
            array('LMI_SYS_INVS_NO,LMI_SYS_TRANS_NO', 'numerical', 'integerOnly'=>true, 'on'=>'paydone'),
            //array('LMI_SYS_TRANS_DATE', 'date', 'format' => array('yyyyMMdd', '00-00-0000'), 'allowEmpty'=>false, 'on'=>'paydone'),
			array('LMI_HASH', 'validateSign', 'on'=>'paydone'),
            array('LMI_SECRET_KEY', 'safe', 'on'=>'paydone'),
		);
	}

	/**
	* Fill options var
	*/
	public function init()
	{
        $this->_options = Payoptions::model()->find(array(
            'select'=>'secret,purse,mode',
            'condition'=>'system=:system',
            'params'=>array(':system'=>'WMR'),
        ));
	}

    public function validatePaymentNo($attribute,$params)
    {
        if (empty($this->LMI_PAYMENT_NO))
        {
            $this->addError('LMI_PAYMENT_NO', 'Поле не может быть пустым');
            return false;
        }
        // проверяем платеж по базе
        $payment = Logbalance::model()->find('order_id=:order_id', array(':order_id'=>$this->LMI_PAYMENT_NO));
        if ($payment === null)
        {
            $this->addError('LMI_PAYMENT_NO', 'Транзакции с таким ID не существует');
            return false;
        }
        if (!in_array($payment->state, array('I','P')))
        {
            $this->addError('LMI_PAYMENT_NO', 'Транзакция с таким ID уже проведена. Повторите платеж заново.');
            return false;
        }
        if ($payment->amount !== $this->LMI_PAYMENT_AMOUNT)
        {
            $this->addError('LMI_PAYMENT_AMOUNT', 'Сумма платежа не соотвествует сумме указанной при оформлении заказа.');
            return false;
        }
    }

    /**
     * Check true sign
     */
    public function validateSign($attribute,$params)
    {
        $sign = $this->_options['purse'].
            $this->LMI_PAYMENT_AMOUNT.
            $this->LMI_PAYMENT_NO.
            $this->LMI_MODE.
            $this->LMI_SYS_INVS_NO.
            $this->LMI_SYS_TRANS_NO.
            $this->LMI_SYS_TRANS_DATE.
            $this->_options['secret'].
            $this->LMI_PAYER_PURSE.
            $this->LMI_PAYER_WM;

        $sign = strtoupper(md5($sign));
        if($sign != $this->LMI_HASH)
        {
            $this->addError('LMI_PAYMENT_NO', 'Хеш-код неверный.');
            return false;
        }
    }
}