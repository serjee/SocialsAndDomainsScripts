<?php
/**
 * SeoChecker helper class.
 *
 * For detect seo parameters of domain
 *
 * @author Bashkov Sergey <ser.bsh@gmail.com>
 * @copyright  Copyright (c) 2013 Bashkov S.
 * @version 1.0, 2013-06-30
 */
class USeoChecker
{
    /**
     * Get Yandex info
     * 
     * @var string $url Domain for get yandex info
     */
    public static function get_yandex_cy($url, $proxy='', $timeout=0)
    {
        $domain = $url;
        $ret = array();
        $ret['tic'] = '0';
        $ret['glue'] = 'no';
        $ret['yaca'] = 'no';
        
    	if( substr($url, 0, 7) != 'http://' ) $url = 'http://' . $url;
    	       
    	if( $content = self::get_download('http://bar-navig.yandex.ru/u?ver=2&show=1&post=0&url='.urlencode($url), $timeout, $proxy) )
        {
            // get ya tic
            preg_match('/<tcy rang=\"\d\" value=\"(\d+)\"\/>/Usi', $content, $tic);             
            if( !empty($tic[1]) )
            {
                $ret['tic'] = $tic[1];
            }
            
            // get ya glue
            preg_match('/<url domain="(.*?)">/Usi', $content, $glue);
            if( !empty($glue[1]) && $glue[1] !== $domain && $glue[1] !== 'www.'.$domain )
            {
                $ret['glue'] = $glue[1];
            }
            
            // get ya catalog
            preg_match('/<topic title=\"(.*?)\" url=\"(.*?)\"\/>/Usi', $content, $yaca);
            if ( !empty ($yaca[1]) )
            {
                $ret['yaca'] = iconv('windows-1251', 'UTF-8', substr($yaca[1],6));
            }
    	}
    	
    	return $ret;
    }
    
    /**
     * Get Google PR
     * 
     * @var string $url Domain for get PR
     */
    public static function get_google_pr($url, $proxy='', $timeout=0)
	{
		$query="http://toolbarqueries.google.com/tbr?client=navclient-auto&ch=".self::CheckHash(self::HashURL($url)). "&features=Rank&q=info:".$url."&num=100&filter=0";
        $data = self::get_download($query, $timeout, $proxy);
		$pos = strpos($data, "Rank_");
		if($pos === false){}
        else
		{
			$pagerank = substr($data, $pos + 9);
			return $pagerank;
		}
	}
	
    /**
     * Get Glue PR
     * 
     * @var string $url Domain for get CY
     */
    public static function get_glue_pr($host, $proxy='', $timeout=0)
    {
        $domain = $host;
    	
    	if( 'www.' == substr($host,0,4) )
        {
    		$domain = substr($host,4);
    	}
    	
    	$url = 'http://www.google.ru/search?q=info:' . urlencode($domain);
    			
    	if( $s = self::get_download($url, $timeout, $proxy) )
        {
    		if( preg_match('/<cite>(.*?)\/<\/cite>/i', $s, $a) )
            {
    			if($a[1] !== $domain && $a[1] !== 'www.'.$domain )
                {
    				return $a[1];
    			}
                else
                {
    				return "no";
    			}
    		}
    	}
    			
    	return 'n/a';
    }
    
    /**
     * Get DMOZ count
     * 
     * @var string $url DMOZ count
     */
    public static function get_dmoz($host, $proxy='', $timeout=0)
    {    
    	$domain = $host;
        $ret = array();
        $ret['stat'] = 'NA';
        $ret['count'] = 0;
    	
    	if( 'www.' == substr($host,0,4) )
        {
    		$domain = substr($host,4);
    	}
    	
    	//$url = 'http://search.dmoz.org/cgi-bin/search?search=' . $domain;
    	$url = 'http://www.dmoz.org/search/?q=' . $domain;
    			
    	if( $s = self::get_download($url, $timeout, $proxy) )
        {
            if( preg_match('/<strong>Open Directory Sites<\/strong>\s*<small>\(\d+\-\d+ of (\d+)\)<\/small>/i', $s, $a) )
            {
                $ret['stat'] = 'YES';
                $ret['count'] = $a[1];
            }
            else
            {
                $ret['stat'] = 'NO';
            }
        }
    			
        return $ret;
    }
    
    /**
     * Get Web Archive placed
     * 
     * @var string $url Placed in web archive
     */
    public static function get_wa($host, $proxy='', $timeout=0)
    {
        $domain = $host;
        $ret = array();
        $ret['stat'] = 'NA';
        $ret['count'] = 0;
    	
    	if( 'www.' == substr($host,0,4) )
        {
    		$domain = substr($host,4);
    	}
    	
    	$url = 'http://web.archive.org/web/*/http://' . $domain;
    	//$url = 'http://web.archive.bibalex.org/web/*/http://' . $domain;
    			
    	if($s = self::get_download($url, $timeout, $proxy))
        {
            //for new interface for webarchive (but it slow):
            //if( preg_match('/<p align="right"><b>(\d+)<\/b> Results<\/p>/i', $s, $a) )
            if( preg_match('/has been crawled <strong>(\d+\s?\d*) times<\/strong> going all the way back to/i', $s, $a) )
            {
                $ret['stat'] = 'YES';
                $ret['count'] = str_replace(" ", "", $a[1]);
            }
            else
            {
                $ret['stat'] = 'NO';
            }
        }

        return $ret;
    }
    
    /**
     * Get download content by URL
     * 
     * @var string $url URL for get download
     */
    private static function get_download($url, $timeout = 0, $proxy = '')
    {
        $ret = false;
		
		if ( function_exists('curl_init') )
        {
			if( $curl = curl_init() )
            {
                if( !curl_setopt($curl, CURLOPT_URL, $url) ) return $ret;
				if( !curl_setopt($curl, CURLOPT_RETURNTRANSFER, true) ) return $ret;
				if( !curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout) ) return $ret;
				if( !curl_setopt($curl, CURLOPT_HEADER, false) ) return $ret;
				if( !curl_setopt($curl, CURLOPT_ENCODING, "gzip,deflate") ) return $ret;
                
                //curl_setopt($curl, CURLOPT_REFERER, @$_SERVER['HTTP_REFERER']);
                curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17');
                                
                if ($proxy !== '')
                {
                    $proxyAddr = self::GetRandomProxy($proxy);
                    if ($proxyAddr)
                    {
                        curl_setopt($curl, CURLOPT_PROXY, $proxyAddr);
                        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout*2);
                    }
                    
                }
				
				$ret = curl_exec($curl);
				
				curl_close($curl);
			}
		}
		
		return $ret;
	}
    
    /**
     * Get Google PR (additional function)
     * 
     * @var 
     */
    private static function StrToNum($Str, $Check, $Magic)
	{
		$Int32Unit = 4294967296; // 2^32
		$length = strlen($Str);
		for ($i = 0; $i < $length; $i++)
		{
			$Check *= $Magic;
			if ($Check >= $Int32Unit)
			{
				$Check = ($Check - $Int32Unit * (int) ($Check / $Int32Unit));
				$Check = ($Check < -2147483648) ? ($Check + $Int32Unit) : $Check;
			}
			$Check += ord($Str{$i});
		}
		return $Check;
	}
    
    /**
     * Get Google PR (additional function)
     * 
     * @var 
     */
	private static function HashURL($String)
	{
		$Check1 = self::StrToNum($String, 0x1505, 0x21);
		$Check2 = self::StrToNum($String, 0, 0x1003F);
		$Check1 >>= 2;
		$Check1 = (($Check1 >> 4) & 0x3FFFFC0 ) | ($Check1 & 0x3F);
		$Check1 = (($Check1 >> 4) & 0x3FFC00 ) | ($Check1 & 0x3FF);
		$Check1 = (($Check1 >> 4) & 0x3C000 ) | ($Check1 & 0x3FFF);
		$T1 = (((($Check1 & 0x3C0) << 4) | ($Check1 & 0x3C)) <<2 ) | ($Check2 & 0xF0F );
		$T2 = (((($Check1 & 0xFFFFC000) << 4) | ($Check1 & 0x3C00)) << 0xA) | ($Check2 & 0xF0F0000 );
		return ($T1 | $T2);
	}
    
    /**
     * Get Google PR (additional function)
     * 
     * @var 
     */
	private static function CheckHash($Hashnum)
	{
		$CheckByte = 0;
		$Flag = 0;
		$HashStr = sprintf('%u', $Hashnum);
		$length = strlen($HashStr);
		for ($i = $length - 1; $i >= 0; $i --)
		{
			$Re = $HashStr{$i};
			if (1 === ($Flag % 2))
			{
				$Re += $Re;
				$Re = (int)($Re / 10) + ($Re % 10);
			}
			$CheckByte += $Re;
			$Flag ++;
		}
		$CheckByte %= 10;
		if (0 !== $CheckByte)
		{
			$CheckByte = 10 - $CheckByte;
			if (1 === ($Flag % 2) )
			{
				if (1 === ($CheckByte % 2))
				{
					$CheckByte += 9;
				}
				$CheckByte >>= 1;
			}
		}
		return '7'.$CheckByte.$HashStr;
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
        $cntProxy = Proxylist::model()->count("timestamp >= :timestamp", array(":timestamp"=>$now));
        
        // UPDATE PROXY
        if ($cntProxy == 0)
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
        
        //echo $getproxy->ip.':'.$getproxy->port."\r\n";
        return $getproxy->ip.':'.$getproxy->port;
    }

} // End class