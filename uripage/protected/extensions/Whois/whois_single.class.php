<?php
/**
 * whois_single class
 * Yii extension for performing whois lookups
 * Copyright (c) 2013 whois_single
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
 * @package whois_single
 * @author Sergey Bashkov
 * @copyright Copyright (c) 2013 whois_single
 * @version 1.0, 2013-07-15
 */

/**
 * Include the the idna_convert class.
 */
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'idna_convert.class.php');

class whois_single
{
	private $domain;
    private $domainname;
    private $tldname;
    private $domainvalid = false;
    private $error;
    
    private $servers = array(
        'рф'    => array('whois.ripn.net', 'No entries found'),
        'ru'    => array('whois.ripn.net', 'No entries found'),
        'su'    => array('whois.ripn.net', 'No entries found'),
        'com'   => array('whois.crsnic.net', 'No match'),
        'net'   => array('whois.crsnic.net', 'No match'),
        'org'   => array('whois.pir.org', 'NOT FOUND'),
        'biz'   => array('whois.biz', 'Not found'),
        'info'  => array('whois.afilias.net', 'Not found'),
        'mobi'  => array('whois.dotmobiregistry.net', 'NOT FOUND'),
        'name'  => array('whois.nic.name', 'No match'),
        'pw'    => array('whois.nic.pw','NOT FOUND'),
        'tv'    => array('whois.nic.tv', 'No match'),
        //'cn'    => array('whois.cnnic.net.cn', 'No entries found'),
        //'tw'    => array('whois.twnic.net', 'NO MATCH TIP'),
        //'in'    => array('whois.inregistry.in', 'NOT FOUND'),
        //'mn'    => array('whois.nic.mn', 'Domain not found'),
        //'cc'    => array('whois.nic.cc', 'No match'),
        //'ws'    => array('whois.worldsite.ws', 'No match for'),
        //'asia'  => array('whois.nic.asia', 'NOT FOUND')
    );

    /**
	 * Set and configure initial parameters
	 * @param string $domain_name Domain name
	 */
	public function __construct($domain_name)
    {
        // check and set parameters
        if (preg_match('/^(http|https?:\/\/)?(www\.)?([\da-zа-я-]+)\.([a-zа-я]{2,6})(\.[a-zа-я]{2,6})?\/?$/ui', $domain_name, $dopart))
        {
            // Set domain and tld name
            $this->domainname = $dopart[3];
            $this->tldname = $dopart[4].$dopart[5];
            
            // Found by tld name if exist in servers list
            if(array_key_exists($this->tldname, $this->servers))
            {
                if(preg_match('/^([\dа-я-]+)$/ui', $this->domainname))
                {
                    $idnzones = array("рф", "su", "com", "net", "org", "pw", "tv");
                    
                    if (in_array($this->tldname, $idnzones))
                    {
                        // Set IDN
                        $this->domainname = $this->punicode_enc($this->domainname);
                        if ($this->tldname === 'рф')
                        {
                            $this->domain = $this->domainname.'.xn--p1ai';
                        }
                        else
                        {
                            $this->domain = $this->domainname.'.'.$this->tldname;
                        }
                        $this->domainvalid = true;
                    }
                    else
                    {
                        $this->error = 'INVALID_IDN_DOMAIN_NAME';
                        $this->domainvalid = false;
                    }
                }
                else
                {
                    $this->domain = $this->domainname.'.'.$this->tldname;
                    $this->domainvalid = true;
                }
            }
            else
            {
                $this->error = 'TLD_NAME_NOT_FOUND_IN_SERVERS_LIST';
            }
        }
        else
        {
            $this->error = 'INVALID_DOMAIN_NAME';
            $this->domainvalid = false;
        }
	}
    
    /**
     * Get info about domain is valid or no
     */
    public function isValid()
    {
        return $this->domainvalid;
    }
    
    /**
     * Get Domain
     */
    public function getDomain()
    {
        return $this->domain;
    }
    
    /**
     * Get Domain Name without TLD
     */
    public function getDomainName()
    {
        return $this->domainname;
    }
    
    /**
     * Get TLD name
     */
    public function getTldName()
    {
        return $this->tldname;
    }
    
    /**
     * Get last error code
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
            case "INVALID_IDN_DOMAIN_NAME":
                return 'При вводе имени русскими буквами была допущена ошибка. Допускаются только буквы, цифры и знак "тире".';
            case "TLD_NAME_NOT_FOUND_IN_SERVERS_LIST":
                return 'Введеная вами зона для домена не найдена в нашей базе и соответственно не может быть проверена.';
            case "IS_NOT_SOCKET_CONNECTION_TO_WHOIS_SERVER":
                return 'К сожалению, на ваш запрос мы не получили ответ от сервера. Пожалуйста ваш запрос еще раз.';
            default:
                return 'Неизвестная ошибка';
        }
    }
    
    /**
     * Get domain info
     */
    public function domainLookup()
    {
        $whois_server = $this->servers[$this->tldname][0];

        // If tldname have been found
        if ($whois_server != '')
        {
            // Getting data
            $data = "";
            
            // Getting whois information
            $fp = @fsockopen($whois_server, 43, $errno, $errstr, 30);
            
            if ($fp)
            {
                @fputs($fp, $this->domain."\r\n");
                @socket_set_timeout($fp, 30);
                
                while ( !feof($fp) )
                {
                    $data .= @fgets($fp, 128);
                }
                @fclose($fp);
            }
            else
            {
                $this->error = 'IS_NOT_SOCKET_CONNECTION_TO_WHOIS_SERVER';
            }
            
            return $data;
        }
        else
        {
            $this->error = 'TLD_NAME_NOT_FOUND_IN_SERVERS_LIST';
            return false;
        }
    }
    
    /**
     * Get info about domain is available or no
     */
    public function isAvailable()
    {
        $whois_string = $this->domainLookup();
        
        if($whois_string)
        {
            $findText = strtolower($this->servers[$this->tldname][1]);
            
            if (strpos(strtolower($whois_string), $findText) !== false)
            {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Encode string to punicode
     */
    private function punicode_enc($stringconv)
    {
        $IDN = new idna_convert();
        return $IDN->encode($stringconv);
    }
    
    /**
     * Dencode string from punicode
     */
    private function punicode_dec($stringconv)
    {
        $IDN = new idna_convert();
        return $IDN->decode($stringconv);
    }
}
?>