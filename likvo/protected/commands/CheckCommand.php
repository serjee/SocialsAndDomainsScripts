<?php
/**
 * ProcdomainsruCommand class - working with list of pending delete domain
 * Yii Console Command for find the best deleted domain
 * Copyright (c) 2013 Bashkov S.S.
 * 
 * USAGE:
 * ./yiic check
 * 
 * @author Bashkov Sergey <ser.bsh@gmail.com>
 * @copyright Copyright (c) 2013 Bashkov S.S.
 * @version 1.0, 2013-07-04
 */
 
class CheckCommand extends CConsoleCommand
{
    private $servers = array(
        'ru' => array('whois.ripn.net', 'No entries found'),
    );
    
    /**
     * Execute current command
     * 
     * @var string $args Command line args
     */
	public function run($args)
	{
        $uploaddir = dirname(Yii::app()->basePath).'/upload/';
        $free = @fopen($uploaddir."logs_freedomain4","w");
        @fputs($free, "[".date("Y-m-d H:i:s")."] INFO: Process started.\r\n");
        
        //$arSymb = array ('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','1','2','3','4','5','6','7','8','9','0');
        $arSymb = array ('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
        
        foreach ($arSymb as $symb1)
        {
            foreach ($arSymb as $symb2)
            {
                foreach ($arSymb as $symb3)
                {
                    foreach ($arSymb as $symb4)
                    {
                        $domain = $symb1.$symb2.$symb3.$symb4.'.ru';
                        if ($this->isAvailable($domain))
                        {
                            @fputs($free, $domain."\r\n");
                        }
                    }
                }
            }
        }
        
        @fputs($free, "[".date("Y-m-d H:i:s")."] INFO: Process done!\r\n");
        @fclose($free);
	}
    
        /**
     * Get domain info
     */
    public function domainLookup($domain)
    {
        $whois_server = $this->servers['ru'][0];

        // If tldname have been found
        if ($whois_server != '')
        {
            // Getting data
            $data = "";
            
            // Getting whois information
            $fp = @fsockopen($whois_server, 43, $errno, $errstr, 30);
            
            if ($fp)
            {
                @fputs($fp, $domain."\r\n");
                @socket_set_timeout($fp, 30);
                
                while ( !feof($fp) )
                {
                    $data .= @fgets($fp, 128);
                }
                @fclose($fp);
            }            
            return $data;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Get info about domain is available or no
     */
    public function isAvailable($domain)
    {
        $whois_string = $this->domainLookup($domain);
        
        if($whois_string)
        {
            $findText = strtolower($this->servers['ru'][1]);
            
            if (strpos(strtolower($whois_string), $findText) !== false)
            {
                return true;
            }
        }
        
        return false;
    }
}