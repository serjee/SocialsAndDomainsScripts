<?php
/**
 * Whois class
 * Yii extension for performing whois lookups
 * Copyright (c) 2013 Whois
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *
 * @package Whois
 * @author Sergey Bashkov
 * @copyright Copyright (c) 2013 Whois
 * @version 1.0, 2013-07-15
 */

/**
 * Include the the whois_single class.
 */
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'whois_single.class.php');

/**
 * Define the name of the price list file
 */
define('DOMAIN_PRICE','domainprice.php');

class Whois
{
    private $domainname;
    private $tldname;
    private $valid = false;
    private $pricelist;
    private $error;
                
    private $domainsTld = array("ru","su","com","net","org","info","name","biz","mobi","tv","pw");
    private $domainsIdnTld = array("рф", "su", "com", "net", "org", "pw", "tv");
    
    const PROFILE_FZRU  = 'FZRU';   // Профиль для физ.лиц и ИП доменов ru/рф/su
    const PROFILE_URRU  = 'URRU';   // Профиль для юр.лиц доменов ru/рф/su
    const PROFILE_INTR  = 'INTR';   // Профиль для интернациональных доменов
    const PROFILE_ASIA  = 'ASIA';   // Профиль для домена .ASIA
    const PROFILE_XXX   = 'XXX';    // Профиль для домена .XXX    
    const PROFILE_PRO   = 'PRO';    // Профиль для домена .PRO
    const PROFILE_EU    = 'EU';     // Профиль для домена .EU
    const PROFILE_US    = 'US';     // Профиль для домена .US
        
    /**
	 * Set and configure initial parameters
	 * @param string $whoisinput Field
	 */
	public function __construct($whoisinput)
    {
        $whoisdata = mb_strtolower(trim($whoisinput), 'utf-8');
        
        if (preg_match('/^(http|https?:\/\/)?(www\.)?([\da-zа-я-]+)(\.[a-zа-я]{2,6})?(\.[a-zа-я]{2,6})?\/?$/ui', $whoisdata, $dopart))
        {
            // Load price list
            //$pricelist = require(Yii::getPathOfAlias('application.config').DIRECTORY_SEPARATOR.DOMAIN_PRICE);
            $pricelist =  CHtml::listData(Pricezone::model()->findAll(array('select'=>'zone,reg_price')), 'zone', 'reg_price');
            $this->setPrice($pricelist);
            
            // Set domain and tld name
            $this->domainname = $dopart[3];
            $dopart[4] = str_replace(".", "", $dopart[4]);
            $this->tldname = $dopart[4].$dopart[5];
            $this->valid = true;
        }
        else
        {
            $this->valid = false;
            $this->error = 'INVALID_DOMAIN_NAME';
        }
    }
    
    /**
	 * Price List parameters
	 * @param array $config Config parameters
	 * @throws CException 
	 */
	private function setPrice($pricelist)
	{
		if(!is_array($pricelist))
			throw new CException("Price list must be an array!");
            
		$this->pricelist=$pricelist;
	}
    
    /**
     * Get information about domains in result input data
     * @return array $data Domains data result
     */
    public function getDomainsData()
    {
        $data = array();
        
        // If tld name is not set
        if($this->tldname === '')
        {
            // if IDN
            if(preg_match('/^([\dа-я-]+)$/ui', $this->domainname))
            {
                foreach($this->domainsIdnTld as $tldname)
                {                            
                    // add domain info to data array
                    array_push($data, $this->getDomainData($tldname));
                }
            }
            else
            {
                foreach($this->domainsTld as $tldname)
                {
                    // add domain info to data array
                    array_push($data, $this->getDomainData($tldname));
                }
            }                    
        }
        else
        {
            // add domain info to data array
            array_push($data, $this->getDomainData($this->tldname));
        }
        
        return $data;
    }
    
    /**
     * Get boolean is valid or no
     */
    public function isValid()
    {
        return $this->valid;
    }
    
    /**
     * Get latest error code
     */
    public function getErrorCode()
    {
        return $this->error;
    }
    
    /**
     * Get error text by error code
     */
    public function getErrorText()
    {
        switch($this->getErrorCode())
        {
            case "INVALID_DOMAIN_NAME":
                return 'При вводе имени была допущена ошибка. Имя может состоять только из латинских или русских букв, цифр и символа "тире".';
            default:
                return 'Неизвестная ошибка';
        }
    }
    
    /**
     * Get domain data info as array
     */
    private function  getDomainData($tldname)
    {
        $domainInfo = array();
        $domainInfo["domain"] = $this->domainname.'.'.$tldname;
        
        $domain = new whois_single( $domainInfo["domain"] );
                
        if($domain->isValid())
        {
            $domainInfo["domaininfo"] = $domain->domainLookup();
            $domainInfo["isavailable"] = $domain->isAvailable();
            $domainInfo["price"] = $this->pricelist['.'.$tldname];
            $domainInfo["tldname"] = $tldname;
            
            switch($tldname)
            {
                case "ru":
                case "su":
                case "рф":
                    $domainInfo["profile"] = self::PROFILE_FZRU;
                    //$domainInfo["profile"] = self::PROFILE_URRU;
                    break;
                case "xxx":
                    $domainInfo["profile"] = self::PROFILE_XXX;
                    break;
                case "pro":
                    $domainInfo["profile"] = self::PROFILE_PRO;
                    break;
                case "eu":
                    $domainInfo["profile"] = self::PROFILE_EU;
                    break;
                case "asia":
                    $domainInfo["profile"] = self::PROFILE_ASIA;
                    break;
                case "us":
                    $domainInfo["profile"] = self::PROFILE_US;
                    break;
                default:
                    $domainInfo["profile"] = self::PROFILE_INTR;
            }
        }
        else
        {
            $domainInfo["errormessage"] = $domain->getErrorText();
        }
        
        return $domainInfo;
    }
}