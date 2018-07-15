<?php

/**
 * This is the model class for table "{{expiredomains}}".
 *
 * The followings are the available columns in table '{{expiredomains}}':
 * @property string $id
 * @property string $domain
 * @property string $cy
 * @property string $pr
 * @property string $dmoz
 * @property string $dmoz_count
 * @property string $wa
 * @property string $wa_count
 * @property string $glue_cy
 * @property string $glue_pr
 * @property string $yaca
 * @property string $date
 */
class Expiredomains extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Expiredomains the static model class
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
		return '{{expiredomains}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            array('domain', 'unique'),
			array('domain, glue_cy, glue_pr', 'length', 'max'=>128),
			array('cy, pr, dmoz_count, wa_count', 'length', 'max'=>10),
			array('dmoz, wa', 'length', 'max'=>3),
			array('yaca', 'length', 'max'=>200),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, domain, cy, pr, dmoz, dmoz_count, wa, wa_count, glue_cy, glue_pr, yaca, date', 'safe', 'on'=>'search'),
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
			'domain' => 'Domain',
			'cy' => 'Cy',
			'pr' => 'Pr',
			'dmoz' => 'Dmoz',
			'dmoz_count' => 'Dmoz Count',
			'wa' => 'Wa',
			'wa_count' => 'Wa Count',
			'glue_cy' => 'Glue Cy',
			'glue_pr' => 'Glue Pr',
			'yaca' => 'Yaca',
			'date' => 'Date',
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
		$criteria->compare('domain',$this->domain,true);
		$criteria->compare('cy',$this->cy,true);
		$criteria->compare('pr',$this->pr,true);
		$criteria->compare('dmoz',$this->dmoz,true);
		$criteria->compare('dmoz_count',$this->dmoz_count,true);
		$criteria->compare('wa',$this->wa,true);
		$criteria->compare('wa_count',$this->wa_count,true);
		$criteria->compare('glue_cy',$this->glue_cy,true);
		$criteria->compare('glue_pr',$this->glue_pr,true);
		$criteria->compare('yaca',$this->yaca,true);
		$criteria->compare('date',$this->date,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}