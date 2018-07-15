<?php

/**
 * This is the model class for table "wmoptions".
 */
class Wmoptions extends CActiveRecord
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
	 * @return Wmoptions the static model class
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
		return 'wmoptions';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('mode,purse,secret', 'required'),
			array('mode', 'numerical', 'integerOnly'=>true),
			array('purse', 'match', 'pattern'=>'/[z,u,r]\d{12}/i','message'=>Yii::t('models','Purse format must be 1 letter and 12 nomber')),
			array('secret', 'length', 'max'=>50),
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
			'id' => 'Id',
			'purse' => Yii::t('WmPayForm','Purse'),
			'secret' => Yii::t('WmPayForm','Secret'),
			'mode' => Yii::t('WmPayForm','Mode'),
		);
	}

	/**
	 * @return array of existing modes
	 */
	public function getModesOptions(){
		return array(
			self::REAL=>Yii::t('WmPayForm','Real WM mode'),
			self::SUCCESS=>Yii::t('WmPayForm','Test WM mode (all success)'),
			self::V_D=>Yii::t('WmPayForm','Test WM mode (80% success, 20% fail)'),
			self::FAIL=>Yii::t('WmPayForm','Test WM mode (all fail)'),
		);
	}

	/**
	 * @return string mode value
	 */
	public function getModeText(){
		return $this->modesOptions[$this->mode];
	}
}