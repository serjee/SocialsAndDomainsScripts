<?php

/**
 * This is the model class for table "{{countries}}".
 *
 * The followings are the available columns in table '{{countries}}':
 * @property string $id
 * @property string $iso
 * @property string $country
 * @property string $country_en
 * @property integer $for_eu
 * @property integer $for_asia
 */
class Countries extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Countries the static model class
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
		return '{{countries}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('iso, country, country_en', 'required'),
			array('for_eu, for_asia', 'numerical', 'integerOnly'=>true),
			array('iso', 'length', 'max'=>2),
			array('country, country_en', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, iso, country, country_en, for_eu, for_asia', 'safe', 'on'=>'search'),
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
			'iso' => 'Iso',
			'country' => 'Country',
			'country_en' => 'Country En',
			'for_eu' => 'For Eu',
			'for_asia' => 'For Asia',
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
		$criteria->compare('iso',$this->iso,true);
		$criteria->compare('country',$this->country,true);
		$criteria->compare('country_en',$this->country_en,true);
		$criteria->compare('for_eu',$this->for_eu);
		$criteria->compare('for_asia',$this->for_asia);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}