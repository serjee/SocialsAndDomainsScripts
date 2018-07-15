<?php

/**
 * This is the model class for table "{{orders}}".
 *
 * The followings are the available columns in table '{{orders}}':
 * @property string $id
 * @property string $user_id
 * @property string $domain
 * @property string $punycode
 * @property string $operation
 * @property string $sum
 * @property string $currency
 * @property string $status
 * @property integer $period
 * @property string $timestamp
 * @property string $update_ts
 *
 * The followings are the available model relations:
 * @property User $user
 */
class Orders extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Orders the static model class
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
		return '{{orders}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, operation, sum, currency, status, timestamp', 'required'),
            array('domain', 'required', 'on'=>'domain'),
			array('period', 'numerical', 'integerOnly'=>true),
			array('user_id', 'length', 'max'=>11),
			array('domain', 'length', 'max'=>67),
			array('punycode', 'length', 'max'=>250),
			array('operation', 'length', 'max'=>8),
			array('sum', 'length', 'max'=>10),
			array('currency', 'length', 'max'=>3),
			array('status', 'length', 'max'=>7),
			array('update_ts', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, domain, punycode, operation, sum, currency, status, period, timestamp, update_ts', 'safe', 'on'=>'search'),
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
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
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
			'domain' => 'Domain',
			'punycode' => 'Punycode',
			'operation' => 'Operation',
			'sum' => 'Sum',
			'currency' => 'Currency',
			'status' => 'Status',
			'period' => 'Period',
			'timestamp' => 'Timestamp',
			'update_ts' => 'Update Ts',
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
		$criteria->compare('domain',$this->domain,true);
		$criteria->compare('punycode',$this->punycode,true);
		$criteria->compare('operation',$this->operation,true);
		$criteria->compare('sum',$this->sum,true);
		$criteria->compare('currency',$this->currency,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('period',$this->period);
		$criteria->compare('timestamp',$this->timestamp,true);
		$criteria->compare('update_ts',$this->update_ts,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}