<?php
/**
 * ApiRegru class - API for REG.RU
 * Yii extension for working with API
 * Copyright (c) 2013 Bashkov S.
 * 
 * USAGE:
 * $api = new ApiRegru('json', 'username', 'password');
 * $response = $api->createDomainByProfile($domain);
 * echo $response['result'];
 * 
 * @author Bashkov Sergey <ser.bsh@gmail.com>
 * @copyright  Copyright (c) 2013 Bashkov S.
 * @version 1.0, 2013-06-30
 */
 
/**
 * Define the name of the config file
 */
define('CONFIG_FILE','regru.php');

class ApiRegru
{
	private $apiurl = 'api.reg.ru/api/regru2/';
    
	private $accept = 'json';
    
	private $username;
    
	private $password;
    
	private $ssl = false;

    /**
	 * API initialization constructor
     * 
     * @var $username string login
     * @var $password string user password
     * @var $data_format string data format (xml or json)
     * @var $ssl boolean define HTTP or SSL connection
	 */
	public function __construct($data_format='json', $username='', $password='')
	{
        // initialize config
		$config=require(Yii::getPathOfAlias('application.config').DIRECTORY_SEPARATOR.CONFIG_FILE);
		$this->setConfig($config);
        
        // set external auth params
        $this->setAuth($username, $password);
        
        // set data format
		$this->setAccept($data_format);
        
        // set connection
		$this->setConnectionType($this->ssl);
	}
    
    /**
	 * Create new domain
	 * @param array $config Config parameters
	 * @throws CException 
	 */
    public function createDomainByProfile($domain, $type='RU.PP', $name='SER(RU)', $proxy='')
    {
        $post_data = array(
            'profile_type' => $type,
            'profile_name' => $name,
            'domain_name' => $domain,
            'nss' => array(
                'ns0' => 'ns1.reg.ru',
                'ns1' => 'ns2.reg.ru',
            ),
            'period' => '1',
            'enduser_ip' => '79.120.123.159',
        );
        return json_decode($this->sendRequest('domain/create', $post_data, true, $proxy), true);
    }
    
	/**
	 * Configure parameters
	 * @param array $config Config parameters
	 * @throws CException 
	 */
	private function setConfig($config)
	{
		if(!is_array($config))
			throw new CException("Configuration options must be an array!");
		foreach($config as $key=>$val)
		{
			$this->$key=$val;
		}
	}
    
    /**
	 * Auth parameters
	 * @param string $username Username for auth to api
     * @param string $password Password for auth to api
	 * @throws CException 
	 */
	private function setAuth($username, $password)
	{
		if ($username!='' && $password!='')
        {
            $this->username = $username;
            $this->password = $password;
        }
	}
    
    /**
	 * Set the data format to be used
	 * @param array $data_format Data format
	 * @throws CException 
	 */
	private function setAccept($data_format)
	{
        if (!in_array($data_format, array('xml', 'json')))
			throw new Exception('Invalid data format!');
		$this->accept = $data_format;
	}
    
    /**
	 * Set the connection type to be used
	 * @param boolean $ssl SSL check type
	 * @throws CException 
	 */
	private function setConnectionType($ssl)
	{
        if($ssl)
			$this->apiurl = 'https://'.$this->apiurl;
		else
			$this->apiurl = 'http://'.$this->apiurl;
	}

	/**
	 * Send requests to API server.
	 * 
     * Using for auth and get data.
	 * This is main function of the class.
     * 
     * @param string $func Api function
     * @param string or array $post_data Json or Xml data for request
     * @param boolean $auth Auth use (true if need auth)
	 * @throws CException 
	 */
	private function sendRequest($func, $post_data, $auth = false, $proxy = '')
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->apiurl . $func );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);		
		curl_setopt($ch, CURLOPT_REFERER, @$_SERVER['HTTP_REFERER']);
		
		if($this->ssl)
		{
			curl_setopt($curl, CURLOPT_SSLVERSION,3);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		}
		
		if ($post_data)
		{
            $post_prefix = ''; // формируем начальные данные POST запроса
            
            if ($auth)
                $post_prefix .= 'username='.$this->username.'&password='.$this->password.'&';
            
			if ($this->accept === 'json')
            {
                if (is_array($post_data))
                {
                    $post_data = json_encode($post_data);
                }                    
                else
                    throw new Exception('Error: invalid post data format for json!');
            }
            
            $post_prefix .= 'input_format='.$this->accept.'&';                
            $post_prefix .= 'input_data=';
				
			curl_setopt($ch, CURLOPT_POST, true); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_prefix . $post_data);
			curl_setopt($ch, CURLOPT_HTTPHEADER, 'Accept: application/' . $this->accept);
		}
        
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17'); 
		
        if ($proxy !== '')
        {
            $proxyAddr = self::GetRandomProxy($proxy);
            if ($proxyAddr)
            {
                curl_setopt($ch, CURLOPT_PROXY, $proxyAddr);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            }
        }        
        
		$out = curl_exec($ch);
		$info = curl_getinfo($ch);

		if (curl_errno($ch))
        {
            if (curl_errno($ch) == 28)
                $out['error_text'] = 'Connection time out';
            else
                throw new Exception( 'HTTP ERROR - ' . curl_errno($ch));
        }
    
		switch ($info['http_code'])
		{
			case '401' :
				throw new Exception('Authorization failed! Invalid username or password!');
				break;

			case '404' :
				throw new Exception('REGRU API: Not found!');
				break;

			case '405' :
				throw new Exception('REGRU API: The function is temporarily unavailable!');
				break;

			case '500' :
				throw new Exception('REGRU API: Internal Error!');
				break;

			case '400' :
				throw new Exception('REGRU API: Bad request!');
				break;

			default :
				return $out;
		}
	}
    
    /**
     * Get random proxy
     * 
     * @return string ip:port
     */
	private static function GetRandomProxy($apiurl)
	{
        // count of proxy in data base
        $now = date('Y-m-d H:i:s');
        $cntProxy = Proxylist::model()->count("timestamp > :timestamp", array(":timestamp"=>$now));
                
        // UPDATE PROXY
        if ($cntProxy < 1)
        {
            // delete all old records
            Proxylist::model()->deleteAll();
            
            // update price list
            if( $curl = curl_init() )
            {
            	curl_setopt($curl, CURLOPT_URL, $apiurl);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HEADER, false);
                $arProxy = json_decode(curl_exec($curl));            
                curl_close($curl);
                
                // count of proxy
                $prCount = count($arProxy);
                
                // if not null then get ip:port
                if ($prCount > 0)
                {
                    foreach($arProxy as $proxy)
                    {
                        $fp = @fsockopen ($proxy->ip, $proxy->port, $errno, $errstr, 5);
                        if ($fp)
                        {
                            $proxylist = new Proxylist;
                            $proxylist->ip = $proxy->ip;
                            $proxylist->port = $proxy->port;
                            $proxylist->timestamp = new CDbExpression('DATE_ADD(NOW(), INTERVAL 3 MINUTE)');
                            $proxylist->save();
                            
                            fclose ($fp);
                        }
                    }
                }
                else return; //by default
            }
        }
        
        // GET PROXY BY RANDOM
        $getproxy = Proxylist::model()->find(array('select'=>'ip,port','limit'=>'1','order'=>'rand()'));
        return $getproxy->ip.':'.$getproxy->port;
    }
}

?>