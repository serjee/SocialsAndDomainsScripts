<?php
/**
 * Yii Extension to work with a payment system QIWI.
 * This extension allows you to interact with the service QIWI over SOAP.
 *  
 * <p>The following functions:</p> 
 * <ul>
 * <li>Creating an account.</li> 
 * <li>Cancel the account.</li>
 * <li>Checking account status.</li>
 * </ul>
 * 
 * CONFIGRATION
 * <code>
 * 'components'	=>	array(
 * 		'ishop'	=>    array(
 * 			'class'	=>    'ext.ishop.IShop',
 * 			'options'	=>		array(
 * 				'login'	    => 		LOGIN,
 * 				'password'	=>		PASSWORD
 * 			)
 * 		)
 * ),
 * <code>
 * 
 * <p>TERMINATION CODES</p>
 * <ul>
 * <li>0    Success</li> 
 * <li>13   Server is busy, please repeat your request later</li> 
 * <li>150  Authorization error (wrong login/password)</li>
 * <li>215  Bill with this txn-id already exists</li>
 * <li>278  Bill list maximum time range exceeded</li>
 * <li>298  No such agent in the system</li>
 * <li>300  Unknown error</li>
 * <li>330  Encryption error</li>
 * <li>370  Maximum allowed concurrent requests overlimit</li> 
 * </ul>
 * 
 * <p>STATUSES REFERENCE</p>
 * <ul>
 * <li>50   Made</li>
 * <li>52   Processing</li> 
 * <li>60   Payed</li>
 * <li>150  Cancelled (Machine error)</li> 
 * <li>160  Cancelled</li>
 * <li>161  Cancelled (Timeout)</li> 
 * </ul>
 * 
 * 
 * @author nek
 * @package IShop
 * @example example/example.php
 * @see http://ishop.qiwi.ru
 * @see https://ishop.qiwi.ru/docs/OnlineStoresProtocols_SOAP_EN.pdf
 */
class IShop extends CApplicationComponent {
    
    /**
     * Shop login (ID)
     * @var integer
     */
    public $login;
    
    /**
     * Shop password 
     * @var string
     */
    public $password;
    
    /**
     * Path to the wsdl document
     * @var string
     */
    public $wsdlPath;
    
    public $options = array();
    
    protected $validOptions = array(
        'login'       =>    array('type' => 'integer'),
        'password'    =>    array('type' => 'string'),
        'wsdlPath'    =>    array('type' => 'string'),
    );
    
    protected static function checkOptions($value, $validOptions) {
		if (!empty($validOptions)) {
			foreach ($value as $key=>$val) {
				if (!array_key_exists($key, $validOptions)) {
                    throw new CException(Yii::t('IShop', '{k} is not a valid option', array('{k}'=>$key)));
                }
                $type = gettype($val);
                if ((!is_array($validOptions[$key]['type']) && ($type != $validOptions[$key]['type'])) || (is_array($validOptions[$key]['type']) && !in_array($type, $validOptions[$key]['type']))) {
                        throw new CException(Yii::t('IShop', '{k} must be of type {t}',
                        array('{k}'=>$key,'{t}'=>$validOptions[$key]['type'])));
                }
                if (($type == 'array') && array_key_exists('elements', $validOptions[$key])) {
                        self::checkOptions($val, $validOptions[$key]['elements']);
                }
			}
		}
	}
	
    protected function defaults() {
		!isset($this->options['login']) ?  $this->login = '' : $this->login = $this->options['login'];
		!isset($this->options['password']) ?  $this->password = '' : $this->password = $this->options['password'];
		!isset($this->options['wsdlPath']) ?  $this->wsdlPath = Yii::getPathOfAlias('ext.ishop.vendor') : $this->wsdlPath = $this->options['wsdlPath'];		
	}
	
    public function init() {
		if (!extension_loaded('soap')) {
			throw new CException( Yii::t('Soap', 'You must have SOAP enabled in order to use this extension.') );
		} else {
			self::checkOptions($this->options, $this->validOptions);
			$this->defaults();
		}
	}
	
	/**
	 * Creating a bill 
	 * 
	 * @param string $user – user ID (MSISDN)  
	 * @param float $amount – amount of bill
	 * @param string $comment – comment to the bill displayed to the user
	 * @param string $txn – unique bill ID
	 * @param boolean $create – flag to create a new user (if he’s not registered in the system yet)
	 * @throws CException
	 * @return number
	 */
	public function createBill( $user, $amount, $comment, $txn, $create = true ) {
	    if (strlen($txn) > 30) {
	        throw new CException(Yii::t('IShop', 'Row size can not exceed 30 bytes.'));
	    }
	    //
	    $service = $this->setService();
	    $params = new createBill();
	    $params->login = $this->login;
	    $params->password = $this->password;
	    $params->user = $user;
	    $params->amount = $amount;
	    $params->comment = $comment;
	    $params->lifetime = date('d.m.Y H:i:s', strtotime('+1 day ago'));
	    $params->txn = $txn;
	    $res = $service->createBill($params);
	    return (integer) $res;
	}
	
	/**
	 * Cancel a bill
	 * 
	 * @param string $txn – unique bill ID
	 * @throws CException
	 * @return number
	 */
	public function cancelBill( $txn ) {
	    if (strlen($txn) > 30) {
	        throw new CException(Yii::t('IShop', 'Row size can not exceed 30 bytes.'));
	    }
	    $service = $this->setService();
	    $params = new cancelBill();
	    $params->login = $this->login;
	    $params->password = $this->password;
	    $params->txn = $txn;
	    $res = $service->cancelBill($params);
	    $return = $res->cancelBillResult;
	    return (integer) $return;
	}
	
	/**
	 * Check bill status
	 * 
	 * @param string $txn – unique bill ID
	 * @throws CException
	 * @return number
	 */
	public function checkBill( $txn ) {
	    if (strlen($txn) > 30) {
	        throw new CException(Yii::t('IShop', 'Row size can not exceed 30 bytes.'));
	    }
	    $service = $this->setService();
	    $params = new checkBill();
	    $params->login = $this->login;
	    $params->password = $this->password;
	    $params->txn = $txn;
	    $res = $service->checkBill($params);
	    $return = $res;
	    return (integer) $return;
	}
	
	/**
	 * Update bill status
	 * 
	 * @param integer $status - bill status
	 */
	public function updateBill( $status = 0 ) {
	    $server = $this->setServer();
	    $params = new IShopServer();
	    $params->updateBill($status);
	    return;
	}
	
	private function setService() {
	    Yii::import('ext.ishop.vendor.IShopServerWSService');
	    $service = new IShopServerWSService($this->wsdlPath.'/IShopServerWS.wsdl',array(
	    	'location' => 'https://ishop.qiwi.ru/services/ishop',
	    	'trace' => 1
	    ));
	    return $service;
	}
	
    private function setServer() {
        Yii::import('ext.ishop.vendor.IShopServer');
	    $server = new SoapServer($this->wsdlPath.'/IShopClientWS.wsdl', array(
	    	'classmap' => array(
	    		'tns:updateBill' => 'Param',
	    		'tns:updateBillResponse' => 'Response'
	        )
	    ));
	    $server->setClass('IShopServer');
	    $server->handle();
	}
} 