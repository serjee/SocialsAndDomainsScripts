<?php

/**
 * This is the model class for table "{{domainprofiles}}".
 *
 * The followings are the available columns in table '{{domainprofiles}}':
 * @property string $id
 * @property string $user_id
 * @property string $type
 * @property string $created
 * @property integer $isdefault
 * @property string $email
 * @property string $phone
 * @property string $phone_sms
 * @property string $phone_fax
 * @property string $name
 * @property string $country
 * @property integer $whois_hide
 * @property string $ru_first_name
 * @property string $ru_last_name
 * @property string $ru_middle_name
 * @property string $birth_date
 * @property string $org_name
 * @property string $org_ru_name
 * @property string $org_inn
 * @property string $org_kpp
 * @property string $org_address
 * @property string $en_first_name
 * @property string $en_last_name
 * @property string $en_middle_name
 * @property string $pasport_num
 * @property string $pasport_iss
 * @property string $pasport_date
 * @property integer $pochta_code
 * @property string $pochta_region
 * @property string $pochta_city
 * @property string $pochta_address
 * @property string $pochta_to
 * @property string $xxx_sponsored
 * @property string $pro_profession
 * @property string $pro_license_number
 * @property string $pro_licensing_auth
 * @property string $pro_auth_website
 *
 * The followings are the available model relations:
 * @property User $user
 */
class Domainprofiles extends CActiveRecord
{    
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Domainprofiles the static model class
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
		return '{{domainprofiles}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            // допустимые символы общих полей
            array('user_id, type, created, email, phone, name, pochta_code, pochta_city, pochta_address, country','required'),
            array('email', 'email'),
            array('phone', 'match', 'pattern'=>'/^[\+]?([\d]{1,3})\s+([\d]{2,3})\s+([\d]{5,10})$/', 'message'=>'Неверный формат. Телефон должен быть в виде +7 495 1234567'),            
            array('country', 'match', 'pattern'=>'/^([A-Z]{2})$/', 'message'=>'Неверный формат. Это поле может содержать только двухсимвольный код страны.'),
            array('pochta_code', 'match', 'pattern'=>'/^([\d]{5,6})$/', 'message'=>'Неверный формат. Это поле может содержать только цифры, от 5 до 6 символов.'),            
            
            // проверка скрытых типов данных от возможной подмены значений
            array('xxx_sponsored, pro_profession, pro_license_number, pro_licensing_auth, pro_auth_website', 'length', 'is'=>0),            
            
            // допустимые символы для профиля русских доменов
            array('ru_first_name, ru_last_name, ru_middle_name, birth_date, pasport_num, pasport_iss, pasport_date, pochta_to', 'required', 'on'=>'FZRU'), // обязательные поля для русских доменов
            array('ru_first_name, ru_last_name, ru_middle_name', 'match', 'pattern'=>'/^([а-яА-Яa-zA-Z]*)$/u', 'message'=>'Неверный формат. Это поле может содержать только русские или латинские буквы', 'on'=>'FZRU'),
            array('birth_date', 'date', 'format' => array('dd-MM-yyyy', '00-00-0000'), 'allowEmpty'=>false, 'on'=>'FZRU'),            
            array('pasport_num', 'match', 'pattern'=>'/^([a-zA-Z\d\s]*)$/', 'message'=>'Неверный формат. Это поле может содержать только латинские буквы, цифры, символы пробела, точки, запятой, дефиса.', 'on'=>'FZRU'),            
            array('pasport_iss', 'match', 'pattern'=>'/^([а-яА-Яa-zA-Z\d\s\.\-]*)$/u', 'message'=>'Неверный формат. Это поле может содержать только латинские буквы, цифры, символы пробела, точки, дефис.', 'on'=>'FZRU'),
            array('pasport_date', 'date', 'format' => array('dd-MM-yyyy', '00-00-0000'), 'allowEmpty'=>false, 'on'=>'FZRU'),            
            array('pochta_region', 'match', 'pattern'=>'/^([а-яА-Яa-zA-Z\d\s\-\.]*)$/u', 'message'=>'Неверный формат. Это поле может содержать только латинские или русские буквы, цифры, символы пробела, точка, дефис.', 'on'=>'FZRU'),
            array('pochta_city', 'match', 'pattern'=>'/^([а-яА-Яa-zA-Z\d\s\-\.]*)$/u', 'message'=>'Неверный формат. Это поле может содержать только латинские или русские буквы, цифры, символы пробела, точка, дефис.', 'on'=>'FZRU'),
            array('pochta_address', 'match', 'pattern'=>'/^([а-яА-Яa-zA-Z\d\s\-\.\,]*)$/u', 'message'=>'Неверный формат. Это поле может содержать только латинские или русские буквы, цифры, символы пробела, точка, запятая, дефис.', 'on'=>'FZRU'),
            array('pochta_to', 'match', 'pattern'=>'/^([а-яА-Яa-zA-Z\s]*)$/u', 'message'=>'Неверный формат. Это поле может содержать только латинские или русские буквы и символы пробела.', 'on'=>'FZRU'),            
            array('org_name, org_ru_name, org_inn, org_kpp, org_address, phone_fax,', 'length', 'is'=>0, 'on'=>'FZRU'),             
            // допустимые символы для профиля интернациональных доменов
            array('en_first_name, en_last_name, org_name', 'required', 'on'=>'INTR'), // обязательные поля для международных доменов
            array('en_first_name, en_last_name', 'match', 'pattern'=>'/^([A-Za-z]*)$/', 'message'=>'Поле может содержать только латинские буквы', 'on'=>'INTR'),
            array('org_name', 'match', 'pattern'=>'/^([A-Za-z\s\.\,\-\"]*)$/', 'message'=>'Поле может содержать только латинские буквы, допускаются символы: пробел, точка, запятая, дефис, кавычки.', 'on'=>'INTR'),
            array('pochta_region', 'match', 'pattern'=>'/^([a-zA-Z\d\s\-\.]*)$/', 'message'=>'Неверный формат. Это поле может содержать только латинские буквы, цифры, символы пробела, точка, дефис.', 'on'=>'INTR'),
            array('pochta_city', 'match', 'pattern'=>'/^([a-zA-Z\d\s\-\.]*)$/', 'message'=>'Неверный формат. Это поле может содержать только латинские буквы, цифры, символы пробела, точка, дефис.', 'on'=>'INTR'),
            array('pochta_address', 'match', 'pattern'=>'/^([a-zA-Z\d\s\-\.\,]*)$/', 'message'=>'Неверный формат. Это поле может содержать только латинские буквы, цифры, символы пробела, точка, запятая, дефис.', 'on'=>'INTR'),
            array('whois_hide', 'boolean', 'on'=>'INTR'),            
            // ограничение на максимальную длинну полей
            array('isdefault, whois_hide, pochta_code', 'numerical', 'integerOnly'=>true),
            array('email, name, pochta_region, pochta_address', 'length', 'max'=>50),
			array('phone, phone_sms, phone_fax, ru_first_name, ru_last_name, ru_middle_name, en_first_name, en_last_name, pasport_num, pochta_city', 'length', 'max'=>20),
			array('org_name, org_ru_name, xxx_sponsored', 'length', 'max'=>255),
			array('org_inn', 'length', 'max'=>12),
			array('org_kpp', 'length', 'max'=>9),
			array('en_middle_name', 'length', 'max'=>1),
			array('pasport_iss, pro_profession, pro_license_number, pro_licensing_auth, pro_auth_website', 'length', 'max'=>100),
			array('pochta_to', 'length', 'max'=>60),
			array('birth_date, pasport_date', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, type, created, isdefault, email, phone, phone_sms, phone_fax, name, country, whois_hide, ru_first_name, ru_last_name, ru_middle_name, birth_date, org_name, org_ru_name, org_inn, org_kpp, org_address, en_first_name, en_last_name, en_middle_name, pasport_num, pasport_iss, pasport_date, pochta_code, pochta_region, pochta_city, pochta_address, pochta_to, xxx_sponsored, pro_profession, pro_license_number, pro_licensing_auth, pro_auth_website', 'safe', 'on'=>'search'),
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
	 * This is invoked before the record is saved.
	 * @return boolean whether the record should be saved.
	 */
	protected function beforeSave()
	{
        if(parent::beforeSave())
		{
            if($this->scenario=='FZRU')
            {
                $this->birth_date = date("Y-m-d", strtotime($this->birth_date));
                $this->pasport_date = date("Y-m-d", strtotime($this->pasport_date));
                if (preg_match('/^[\+]?([\d]{1,3})\s+([\d]{2,3})\s+([\d]{5,10})$/', $this->phone, $arphone))
                {
                    $this->phone = '+'.$arphone[1].' '.$arphone[2].' '.$arphone[3]; //формат +0 000 0000000
                    $this->phone_sms = $this->phone;
                }
            }
            else
            {
                if (preg_match('/^[\+]?([\d]{1,3})\s+([\d]{2,3})\s+([\d]{5,10})$/', $this->phone, $arphone))
                {
                    $this->phone = '+'.$arphone[1].'.'.$arphone[2].$arphone[3]; //формат +0.0000000000
                }
            }
            return true;
		}
		else
			return false;
    }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
            'id' => 'ID',
			'user_id' => 'User ID',
			'type' => 'Тип профиля',
			'created' => 'Создан',
			'isdefault' => 'Профиль по умолчанию',
			'email' => 'Email',
			'phone' => 'Телефон',
			'phone_sms' => 'Телефон для SMS уведомлений',
			'phone_fax' => 'Факс',
			'name' => 'Название профиля',
			'country' => 'Страна',
			'whois_hide' => 'Скрывать в whois',
			'ru_first_name' => 'Имя',
			'ru_last_name' => 'Фамилия',
			'ru_middle_name' => 'Отчество',
			'birth_date' => 'Дата рождения',
			'org_name' => 'Компания',
			'org_ru_name' => 'Компания (по-русски)',
			'org_inn' => 'ИНН',
			'org_kpp' => 'КПП',
			'org_address' => 'Юр.адрес',
			'en_first_name' => 'Имя',
			'en_last_name' => 'Фамилия',
			'en_middle_name' => 'Отчество',
			'pasport_num' => 'Серия и номер',
			'pasport_iss' => 'Кем выдан',
			'pasport_date' => 'Дата вытачи',
			'pochta_code' => 'Индекс',
			'pochta_region' => 'Область/Штат',
			'pochta_city' => 'Город/Нас. пункт',
			'pochta_address' => 'Улица, дом, квартира',
			'pochta_to' => 'Получатель',
			'xxx_sponsored' => 'Xxx Sponsored',
			'pro_profession' => 'Pro Profession',
			'pro_license_number' => 'Pro License Number',
			'pro_licensing_auth' => 'Pro Licensing Auth',
			'pro_auth_website' => 'Pro Auth Website',
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
		$criteria->compare('type',$this->type,true);
		$criteria->compare('created',$this->created,true);
		$criteria->compare('isdefault',$this->isdefault,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('phone_sms',$this->phone_sms,true);
		$criteria->compare('phone_fax',$this->phone_fax,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('country',$this->country,true);
		$criteria->compare('whois_hide',$this->whois_hide);
		$criteria->compare('ru_first_name',$this->ru_first_name,true);
		$criteria->compare('ru_last_name',$this->ru_last_name,true);
		$criteria->compare('ru_middle_name',$this->ru_middle_name,true);
		$criteria->compare('birth_date',$this->birth_date,true);
		$criteria->compare('org_name',$this->org_name,true);
		$criteria->compare('org_ru_name',$this->org_ru_name,true);
		$criteria->compare('org_inn',$this->org_inn,true);
		$criteria->compare('org_kpp',$this->org_kpp,true);
		$criteria->compare('org_address',$this->org_address,true);
		$criteria->compare('en_first_name',$this->en_first_name,true);
		$criteria->compare('en_last_name',$this->en_last_name,true);
		$criteria->compare('en_middle_name',$this->en_middle_name,true);
		$criteria->compare('pasport_num',$this->pasport_num,true);
		$criteria->compare('pasport_iss',$this->pasport_iss,true);
		$criteria->compare('pasport_date',$this->pasport_date,true);
		$criteria->compare('pochta_code',$this->pochta_code);
		$criteria->compare('pochta_region',$this->pochta_region,true);
		$criteria->compare('pochta_city',$this->pochta_city,true);
		$criteria->compare('pochta_address',$this->pochta_address,true);
		$criteria->compare('pochta_to',$this->pochta_to,true);
		$criteria->compare('xxx_sponsored',$this->xxx_sponsored,true);
		$criteria->compare('pro_profession',$this->pro_profession,true);
		$criteria->compare('pro_license_number',$this->pro_license_number,true);
		$criteria->compare('pro_licensing_auth',$this->pro_licensing_auth,true);
		$criteria->compare('pro_auth_website',$this->pro_auth_website,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}