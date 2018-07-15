<?php

/**
 * This is the model class for table "{{pricezone}}".
 *
 * The followings are the available columns in table '{{pricezone}}':
 * @property integer $id
 * @property integer $price_id
 * @property string $zone
 * @property string $reg_price
 * @property string $rereg_price
 * @property integer $regis_reg_price
 * @property integer $regis_rereg_price
 * @property integer $enabled
 * @property integer $bonus_sum
 * @property integer $sort_id
 */
class Pricezone extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Pricezone the static model class
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
		return '{{pricezone}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('zone, reg_price, rereg_price', 'required'),
			array('price_id, regis_reg_price, regis_rereg_price, enabled, bonus_sum, sort_id', 'numerical', 'integerOnly'=>true),
			array('zone', 'length', 'max'=>10),
			array('reg_price, rereg_price', 'length', 'max'=>6),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, price_id, zone, reg_price, rereg_price, regis_reg_price, regis_rereg_price, enabled, bonus_sum, sort_id', 'safe', 'on'=>'search'),
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
			'price_id' => 'Price',
			'zone' => 'Zone',
			'reg_price' => 'Reg Price',
			'rereg_price' => 'Rereg Price',
			'regis_reg_price' => 'Regis Reg Price',
			'regis_rereg_price' => 'Regis Rereg Price',
			'enabled' => 'Enabled',
			'bonus_sum' => 'Bonus Sum',
			'sort_id' => 'Sort',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('price_id',$this->price_id);
		$criteria->compare('zone',$this->zone,true);
		$criteria->compare('reg_price',$this->reg_price,true);
		$criteria->compare('rereg_price',$this->rereg_price,true);
		$criteria->compare('regis_reg_price',$this->regis_reg_price);
		$criteria->compare('regis_rereg_price',$this->regis_rereg_price);
		$criteria->compare('enabled',$this->enabled);
		$criteria->compare('bonus_sum',$this->bonus_sum);
		$criteria->compare('sort_id',$this->sort_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}