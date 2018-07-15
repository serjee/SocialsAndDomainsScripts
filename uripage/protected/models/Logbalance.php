<?php

/**
 * This is the model class for table "{{logbalance}}".
 *
 * The followings are the available columns in table '{{logbalance}}':
 * @property string $id
 * @property string $user_id
 * @property string $order_id
 * @property string $pin_id
 * @property string $amount
 * @property string $amount_usd
 * @property string $in_out
 * @property string $pay_system
 * @property string $payed_type
 * @property string $state
 * @property string $timestamp
 * @property integer $lmi_sys_invs_no
 * @property integer $lmi_sys_trans_no
 * @property string $lmi_sys_trans_date
 * @property string $lmi_payer_purse
 * @property string $lmi_payer_wm
 * @property string $lmi_sys_payment_id
 * @property string $lmi_sys_payment_date
 */
class Logbalance extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Logbalance the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{logbalance}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, order_id, amount, in_out, pay_system, payed_type, timestamp', 'required'),
			array('lmi_sys_invs_no, lmi_sys_trans_no', 'numerical', 'integerOnly'=>true),
			array('user_id, order_id, pin_id', 'length', 'max'=>11),
			array('amount, amount_usd', 'length', 'max'=>10),
			array('in_out', 'length', 'max'=>3),
			array('pay_system', 'length', 'max'=>6),
			array('payed_type', 'length', 'max'=>5),
			array('state', 'length', 'max'=>1),
			array('lmi_payer_purse, lmi_payer_wm', 'length', 'max'=>20),
			array('lmi_sys_payment_id, lmi_sys_payment_date', 'length', 'max'=>255),
			array('lmi_sys_trans_date', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, order_id, pin_id, amount, amount_usd, in_out, pay_system, payed_type, state, timestamp, lmi_sys_invs_no, lmi_sys_trans_no, lmi_sys_trans_date, lmi_payer_purse, lmi_payer_wm, lmi_sys_payment_id, lmi_sys_payment_date', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'User',
			'order_id' => 'Order',
			'pin_id' => 'Pin',
			'amount' => 'Amount',
			'amount_usd' => 'Amount Usd',
			'in_out' => 'In Out',
			'pay_system' => 'Pay System',
			'payed_type' => 'Payed Type',
			'state' => 'State',
			'timestamp' => 'Timestamp',
			'lmi_sys_invs_no' => 'Lmi Sys Invs No',
			'lmi_sys_trans_no' => 'Lmi Sys Trans No',
			'lmi_sys_trans_date' => 'Lmi Sys Trans Date',
			'lmi_payer_purse' => 'Lmi Payer Purse',
			'lmi_payer_wm' => 'Lmi Payer Wm',
			'lmi_sys_payment_id' => 'Lmi Sys Payment',
			'lmi_sys_payment_date' => 'Lmi Sys Payment Date',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('user_id',$this->user_id,true);
		$criteria->compare('order_id',$this->order_id,true);
		$criteria->compare('pin_id',$this->pin_id,true);
		$criteria->compare('amount',$this->amount,true);
		$criteria->compare('amount_usd',$this->amount_usd,true);
		$criteria->compare('in_out',$this->in_out,true);
		$criteria->compare('pay_system',$this->pay_system,true);
		$criteria->compare('payed_type',$this->payed_type,true);
		$criteria->compare('state',$this->state,true);
		$criteria->compare('timestamp',$this->timestamp,true);
		$criteria->compare('lmi_sys_invs_no',$this->lmi_sys_invs_no);
		$criteria->compare('lmi_sys_trans_no',$this->lmi_sys_trans_no);
		$criteria->compare('lmi_sys_trans_date',$this->lmi_sys_trans_date,true);
		$criteria->compare('lmi_payer_purse',$this->lmi_payer_purse,true);
		$criteria->compare('lmi_payer_wm',$this->lmi_payer_wm,true);
		$criteria->compare('lmi_sys_payment_id',$this->lmi_sys_payment_id,true);
		$criteria->compare('lmi_sys_payment_date',$this->lmi_sys_payment_date,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}