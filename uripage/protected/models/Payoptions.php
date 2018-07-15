<?php

/**
 * This is the model class for table "{{payoptions}}".
 *
 * The followings are the available columns in table '{{payoptions}}':
 * @property string $id
 * @property string $system
 * @property string $purse
 * @property string $secret
 * @property string $mode
 */
class Payoptions extends CActiveRecord
{
    /**
     * The followings are the available columns in table 'wmoptions':
     * @var integer $id
     * @var string $purse
     * @var string $secret
     * @var integer $mode
     */
    const SUCCESS=0;
    const REAL=9;
    const V_D=2;
    const FAIL=1;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Payoptions the static model class
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
		return '{{payoptions}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('system, purse, secret, mode', 'required'),
            array('mode', 'numerical', 'integerOnly'=>true),
			array('system', 'length', 'max'=>10),
			array('purse', 'length', 'max'=>13),
			array('secret', 'length', 'max'=>50),
			array('mode', 'length', 'max'=>1),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, system, purse, secret, mode', 'safe', 'on'=>'search'),
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
			'system' => 'System',
			'purse' => 'Purse',
			'secret' => 'Secret',
			'mode' => 'Mode',
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
		$criteria->compare('system',$this->system,true);
		$criteria->compare('purse',$this->purse,true);
		$criteria->compare('secret',$this->secret,true);
		$criteria->compare('mode',$this->mode,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}