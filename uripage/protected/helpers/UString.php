<?php
/**
 * Array helper class.
 *
 * $Id: arr.php 3769 2008-12-15 00:48:56Z zombor $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class UString {
    
    /**
	 * Return a generated password
	 *
	 * @param   integer  length symbols
	 * @return  string password string
	 */
    public static function generate_password($length=10)
    {
        $password = null;
        
        $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
        $size = strlen($chars)-1;
        
        while($length--)
        {
            $password .= $chars[rand(0,$size)];
        }
        
        return $password;         
    }

    /**
     * Return a generated word
     *
     * @param   integer  length symbols
     * @return  string word string
     */
    public static function get_generate_word($length=6)
    {
        $word = null;

        $chars = "QAZXSWEDCVFRTGBNHYUJMKIOLP";
        $size = strlen($chars)-1;

        while($length--)
        {
            $word .= $chars[rand(0,$size)];
        }

        return $word;
    }

    /**
     * Return a translit word
     *
     * @param   integer  length symbols
     * @return  string password string
     */
    public static function get_in_translit($string)
    {
    	$replace=array(
    		"'"=>"","`"=>"","а"=>"a","А"=>"a","б"=>"b","Б"=>"b","в"=>"v","В"=>"v",
    		"г"=>"g","Г"=>"g","д"=>"d","Д"=>"d","е"=>"e","Е"=>"e","ж"=>"zh","Ж"=>"zh",
    		"з"=>"z","З"=>"z","и"=>"i","И"=>"i","й"=>"y","Й"=>"y","к"=>"k","К"=>"k",
    		"л"=>"l","Л"=>"l","м"=>"m","М"=>"m","н"=>"n","Н"=>"n","о"=>"o","О"=>"o",
    		"п"=>"p","П"=>"p","р"=>"r","Р"=>"r","с"=>"s","С"=>"s","т"=>"t","Т"=>"t",
    		"у"=>"u","У"=>"u","ф"=>"f","Ф"=>"f","х"=>"h","Х"=>"h","ц"=>"c","Ц"=>"c",
    		"ч"=>"ch","Ч"=>"ch","ш"=>"sh","Ш"=>"sh","щ"=>"sch","Щ"=>"sch","ъ"=>"","Ъ"=>"",
    		"ы"=>"y","Ы"=>"y","ь"=>"","Ь"=>"","э"=>"e","Э"=>"e","ю"=>"yu","Ю"=>"yu",
    		"я"=>"ya","Я"=>"ya","і"=>"i","І"=>"i","ї"=>"yi","Ї"=>"yi","є"=>"e","Є"=>"e"
    	);
    	return $str=iconv("UTF-8","UTF-8//IGNORE",strtr($string,$replace));
    }
    
    public static function get_in_translate_to_en($string, $gost=false)
    {
        if($gost)
        {
            $replace = array("А"=>"A","а"=>"a","Б"=>"B","б"=>"b","В"=>"V","в"=>"v","Г"=>"G","г"=>"g","Д"=>"D","д"=>"d",
                "Е"=>"E","е"=>"e","Ё"=>"E","ё"=>"e","Ж"=>"Zh","ж"=>"zh","З"=>"Z","з"=>"z","И"=>"I","и"=>"i",
                "Й"=>"I","й"=>"i","К"=>"K","к"=>"k","Л"=>"L","л"=>"l","М"=>"M","м"=>"m","Н"=>"N","н"=>"n","О"=>"O","о"=>"o",
                "П"=>"P","п"=>"p","Р"=>"R","р"=>"r","С"=>"S","с"=>"s","Т"=>"T","т"=>"t","У"=>"U","у"=>"u","Ф"=>"F","ф"=>"f",
                "Х"=>"Kh","х"=>"kh","Ц"=>"Tc","ц"=>"tc","Ч"=>"Ch","ч"=>"ch","Ш"=>"Sh","ш"=>"sh","Щ"=>"Shch","щ"=>"shch",
                "Ы"=>"Y","ы"=>"y","Э"=>"E","э"=>"e","Ю"=>"Iu","ю"=>"iu","Я"=>"Ia","я"=>"ia","ъ"=>"","ь"=>"");
        }
        else
        {
            $arStrES = array("ае","уе","ое","ые","ие","эе","яе","юе","ёе","ее","ье","ъе","ый","ий");
            $arStrOS = array("аё","уё","оё","ыё","иё","эё","яё","юё","ёё","её","ьё","ъё","ый","ий");        
            $arStrRS = array("а$","у$","о$","ы$","и$","э$","я$","ю$","ё$","е$","ь$","ъ$","@","@");
                    
            $replace = array("А"=>"A","а"=>"a","Б"=>"B","б"=>"b","В"=>"V","в"=>"v","Г"=>"G","г"=>"g","Д"=>"D","д"=>"d",
                "Е"=>"Ye","е"=>"e","Ё"=>"Ye","ё"=>"e","Ж"=>"Zh","ж"=>"zh","З"=>"Z","з"=>"z","И"=>"I","и"=>"i",
                "Й"=>"Y","й"=>"y","К"=>"K","к"=>"k","Л"=>"L","л"=>"l","М"=>"M","м"=>"m","Н"=>"N","н"=>"n",
                "О"=>"O","о"=>"o","П"=>"P","п"=>"p","Р"=>"R","р"=>"r","С"=>"S","с"=>"s","Т"=>"T","т"=>"t",
                "У"=>"U","у"=>"u","Ф"=>"F","ф"=>"f","Х"=>"Kh","х"=>"kh","Ц"=>"Ts","ц"=>"ts","Ч"=>"Ch","ч"=>"ch",
                "Ш"=>"Sh","ш"=>"sh","Щ"=>"Shch","щ"=>"shch","Ъ"=>"","ъ"=>"","Ы"=>"Y","ы"=>"y","Ь"=>"","ь"=>"",
                "Э"=>"E","э"=>"e","Ю"=>"Yu","ю"=>"yu","Я"=>"Ya","я"=>"ya","@"=>"y","$"=>"ye");
                
            $string = str_replace($arStrES, $arStrRS, $string);
            $string = str_replace($arStrOS, $arStrRS, $string);
        }
        
        return iconv("UTF-8","UTF-8//IGNORE",strtr($string,$replace));
    }
}