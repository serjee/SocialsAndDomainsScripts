<?php

/**
 * This is the model class for table "{{proxylist}}".
 *
 * The followings are the available columns in table '{{proxylist}}':
 * @property string $ip
 * @property integer $port
 * @property string $timestamp
 */
class Proxylist extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Proxylist the static model class
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
		return '{{proxylist}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('port', 'numerical', 'integerOnly'=>true),
			array('ip', 'length', 'max'=>18),
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
			'ip' => 'Ip',
			'port' => 'Port',
			'timestamp' => 'Timestamp',
		);
	}
}