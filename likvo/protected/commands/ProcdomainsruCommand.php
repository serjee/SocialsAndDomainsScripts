<?php
/**
 * ProcdomainsruCommand class - working with list of pending delete domain
 * Yii Console Command for find the best deleted domain
 * Copyright (c) 2013 Bashkov S.S.
 * 
 * USAGE:
 * ./yiic procdomainsru
 * 
 * @author Bashkov Sergey <ser.bsh@gmail.com>
 * @copyright Copyright (c) 2013 Bashkov S.S.
 * @version 1.0, 2013-07-04
 */
 
class ProcdomainsruCommand extends CConsoleCommand
{
    private $flogs;
    
    /**
     * Execute current command
     * 
     * @var string $args Command line args
     */
	public function run($args)
	{
        // name and path values
        $url = 'http://cctld.ru/files/docs/pendingdelete/RUDelList'.date("Ymd").'.zip';
        //$url = 'http://cctld.ru/files/docs/pendingdelete/RUDelList20130705.zip';
        $pathinfo = pathinfo($url);
        $uploaddir = dirname(Yii::app()->basePath).'/upload/';
        
        // Создаем файл для записи лога
        $this->flogs = @fopen($uploaddir."logs_procdomainsru","w");
        @fputs($this->flogs, "[".date("Y-m-d H:i:s")."] INFO: Process started.\r\n");
        
        // exit if file not found
        if (!@fopen($url, "r"))
        {
            @fputs($this->flogs, "[".date("Y-m-d H:i:s")."] ERROR: Cannot found remote file '".$url."'\r\n");
            @fputs($this->flogs, "[".date("Y-m-d H:i:s")."] INFO: Process done!\r\n");
            @fclose($this->flogs);
            return;
        }
        
        // download file
        $zipfile = $this->fileDownload($uploaddir.$pathinfo['basename'], $url);        
        
        // if download file exist
        $domainsfile = $this->fileUnzip($zipfile, $uploaddir);
        
        // exit if file had not been unziped
        if (!$domainsfile)
        {
            @fputs($this->flogs, "[".date("Y-m-d H:i:s")."] ERROR: Cannot found domains list '".$domainsfile."'\r\n");
            @fputs($this->flogs, "[".date("Y-m-d H:i:s")."] INFO: Process done!\r\n");
            @fclose($this->flogs);
            return; 
        }
        
        // processing domains from list
        $this->processingDomains($domainsfile);
        
        @fputs($this->flogs, "[".date("Y-m-d H:i:s")."] INFO: Process done!\r\n");
        @fclose($this->flogs);
	}
    
    /**
     * Download remote zip file
     * 
     * @var string $filename Out file name
     * @var string $url Remote URL
     */
    private function fileDownload($filename, $url)
    {
        $curl = curl_init();
        $file = fopen($filename, 'w');
        curl_setopt($curl, CURLOPT_URL, $url); #input
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FILE, $file); #output
        curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17');
        curl_exec($curl);
        curl_close($curl);
        fclose($file);
        
        return $filename;
    }
    
    /**
     * Unzip file
     * 
     * @var string $zipfile ZIP file name
     * @var string $uploaddir Path for upload
     */
    private function fileUnzip($zipfile, $uploaddir)
    {
        // if download file exist
        if (file_exists($zipfile))
        {
            // unzip downloaded file
            $zip = new ZipArchive;
            if ($zip->open($zipfile) === TRUE)
            {
                $zip->extractTo($uploaddir);
                $zip->close();
                
                // delete zip file
                @unlink($zipfile);
                
                return str_replace(".zip", ".txt", $zipfile);
            }
        }
        
        @fputs($this->flogs, "[".date("Y-m-d H:i:s")."] Error: Cannot unzip file '".$zipfile."'\r\n");
        return false;
    }
    
    /**
     * Processing domains list
     * 
     * @var string $domainsfile Domains file list
     */
    private function processingDomains($domainsfile)
    {
        // STEP 1: GET DOMAINS AND FIRST CHECKING
        
        // delete all old records
        Expiredomains::model()->deleteAll();
        
        // open domains file
        $file_handle = fopen($domainsfile, "r");
        
        // read file by line
        while (!feof($file_handle))
        {
            // remove eol symbols from domain name
            $domainfree = str_replace(array("\n", "\r"), "", fgets($file_handle));           
            
            // if domain name is not empty, to process
            if ($domainfree != '')
            {
                // get array of need data
                $ya = USeoChecker::get_yandex_cy($domainfree);
                $pr = USeoChecker::get_google_pr($domainfree, Yii::app()->params['proxyapi'], 5);
                
                // filter by cy and pr (only not null)
                if ( $ya['tic'] > 0 || $pr > 0 )
                {
                    // new object of model
                    $expiredomains = new Expiredomains;                
                    // set domain name
                    $expiredomains->domain = $domainfree;
                    // set ya tic
                    $expiredomains->cy = $ya['tic'];
                    // set google pr
                    $expiredomains->pr = $pr;
                    // check dmoz                   
                    if (Yii::app()->params['isdmoz'])
                    {
                        $dz = USeoChecker::get_dmoz($domainfree);
                        // set is dmoz 
                        $expiredomains->dmoz = $dz['stat'];
                        // set dmoz count
                        $expiredomains->dmoz_count = $dz['count'];
                    }
                    // check web archive
                    if (Yii::app()->params['iswa'])
                    {
                        $wa = USeoChecker::get_wa($domainfree);
                        // set is web archive
                        $expiredomains->wa = $wa['stat'];
                        // set web archive count
                        $expiredomains->wa_count =$wa['count'];
                    }                    
                    // set ya glue
                    $expiredomains->glue_cy = $ya['glue'];
                    // set google glue
                    $expiredomains->glue_pr = USeoChecker::get_glue_pr($domainfree);
                    // set ya catalog
                    $expiredomains->yaca = $ya['yaca'];
                    // set datetime
                    $expiredomains->date = new CDbExpression('NOW()');
                    // save model
                    if($expiredomains->save())
                    {
                        //@fputs($this->flogs, "[".date("Y-m-d H:i:s")."] NOTICE: Saved data for domain - ".$domainfree."\r\n");
                    }
                    else
                    {
                        @fputs($this->flogs, "[".date("Y-m-d H:i:s")."] ERROR: Cannot save data for domain: '".$domainfree."', errors: ".$expiredomains->errors."\r\n");
                    }
                } // end if
            } // end if
        } // end while
        
        // close file handle and delete it
        fclose($file_handle);
        @unlink($domainsfile);
        @fputs($this->flogs, "[".date("Y-m-d H:i:s")."] INFO: Step 1 has been done.\r\n");
        
        // STEP 2: FILTERING AND REPEAT CHECKING
        
        // delete all old records
        Interdomains::model()->deleteAll();
        
        // Load data from model
        $esortdomains = Expiredomains::model()->findAll();
        foreach ($esortdomains as $edomain)
        {
            // set additional filtres
            
            // check min CY and PR
            if ($edomain->cy < Yii::app()->params['minCy']) continue;
            if ($edomain->pr < Yii::app()->params['minPr']) continue;
            
            // check min CY and PR
            if (Yii::app()->params['cyPr'])
            {
                if ( $edomain->cy < Yii::app()->params['minCy'] && $edomain->pr < Yii::app()->params['minPr'] )
                {
                    // addon check if CY > minCY*2
                    if ($edomain->cy < (Yii::app()->params['minCy']*2)) continue;
                }
            }
            
            // double check yacy
            if (Yii::app()->params['iscy'])
            {
                $eya = USeoChecker::get_yandex_cy($edomain->domain);
                
                // double check tic
                if(((int)$edomain->cy !== (int)$eya['tic'])) continue;                
                
                // check GLUE CY
                if ($edomain->glue_cy !== $eya['glue']) continue;
                if ($edomain->glue_cy !== 'no' && $edomain->glue_cy !== 'n/a') continue;
            }
            
            // double ckeck google pr
            if (Yii::app()->params['ispr'])
            {
                $epr = USeoChecker::get_google_pr($edomain->domain, Yii::app()->params['proxyapi'], 5);
                
                // double check pr
                if(((int)$edomain->pr !== (int)$epr)) continue;
                
                // check GLUE PR
                $glpr = USeoChecker::get_glue_pr($edomain->domain);
                if ($edomain->glue_pr !== $glpr) continue;
                if ($edomain->glue_pr !== 'no' && $edomain->glue_pr !== 'n/a') continue;
            }
            
            // check dmoz
            if (Yii::app()->params['isdmoz2'])
            {
                $edz = USeoChecker::get_dmoz($edomain->domain);
                if (Yii::app()->params['isdmoz'] && $edomain->dmoz !== $edz['stat']) continue;
                if ($edz['stat'] !== 'YES' && $edz['count'] < 1) continue;
                
                // set value
                $edomain->dmoz = $edz['stat'];
                $edomain->dmoz_count = $edz['count'];
            }
            
            // check web archive
            if (Yii::app()->params['iswa2'])
            {
                $ewa = USeoChecker::get_wa($edomain->domain);
                if (Yii::app()->params['iswa'] && $edomain->wa !== $ewa['stat']) continue;
                if ($ewa['stat'] !== 'YES' && $ewa['count'] < 1) continue;
                
                // set value
                $edomain->wa = $ewa['stat'];
                $edomain->wa_count = $ewa['count'];
            }
            
            // save to model
            $interdomains = new Interdomains;
            // set domain name
            $interdomains->domain = $edomain->domain;
            // set ya tic
            $interdomains->cy = $edomain->cy;
            // set google pr
            $interdomains->pr = $edomain->pr;
            // set is dmoz
            $interdomains->dmoz = $edomain->dmoz;
            // set dmoz count
            $interdomains->dmoz_count = $edomain->dmoz_count;
            // set is web archive
            $interdomains->wa = $edomain->wa;
            // set web archive count
            $interdomains->wa_count = $edomain->wa_count;
            // set ya glue
            $interdomains->glue_cy = $edomain->glue_cy;
            // set google glue
            $interdomains->glue_pr = $edomain->glue_pr;
            // set ya catalog
            $interdomains->yaca = $edomain->yaca;
            // set ya catalog
            $interdomains->interception = 1;
            // set datetime
            $interdomains->date = new CDbExpression('NOW()');
            // save model
            if($interdomains->save())
            {
                // E-mail data
                $message .= '<tr>';
                $message .= '<td valign="top"><p>'.$edomain->domain.'</p></td>';
                $message .= '<td valign="top"><p>'.$edomain->cy.'</p></td>';
                $message .= '<td valign="top"><p>'.$edomain->pr.'</p></td>';
                $message .= '<td valign="top"><p>'.$edomain->dmoz.'</p></td>';
                $message .= '<td valign="top"><p>'.$edomain->dmoz_count.'</p></td>';
                $message .= '<td valign="top"><p>'.$edomain->wa.'</p></td>';
                $message .= '<td valign="top"><p>'.$edomain->wa_count.'</p></td>';
                $message .= '<td valign="top"><p>'.$edomain->glue_cy.'</p></td>';
                $message .= '<td valign="top"><p>'.$edomain->glue_pr.'</p></td>';
                $message .= '<td valign="top"><p>'.$edomain->yaca.'</p></td>';
                $message .= '</tr>';
            }
            else
            {
                @fputs($this->flogs, "[".date("Y-m-d H:i:s")."] ERROR: Cannot save checked data for domain: ".$edomain->domain.", error: ".$interdomains->errors."\r\n");
            }
        }
        @fputs($this->flogs, "[".date("Y-m-d H:i:s")."] INFO: Step 2 has been done.\r\n");
        
        // STEP 3: SEND NOTICE TO E-MAIL
        
        if ($message === '') return; // exit if message is empty
        
        //get template 'domains' from /themes/default/views/mail
        $mail = new YiiMailer('domains', array('message'=>$message, 'title'=>'Освобождающиеся домены', 'date'=>date("d-m-Y")));
        
        //render HTML mail, layout is set from config file or with $mail->setLayout('layoutName')
        $mail->render();
        
        //set properties as usually with PHPMailer
        $mail->From = Yii::app()->params['emailFrom'];
        $mail->FromName = Yii::app()->params['fromNameEmail'];
        $mail->Subject = 'Освобождающиеся домены';
        $mail->AddAddress(Yii::app()->params['emailTo']);
        
        //send
        if ($mail->Send())
        {
            $mail->ClearAddresses();
		}
        else
        {
            @fputs($this->flogs, "[".date("Y-m-d H:i:s")."] ERROR: While sending email, error: ".$mail->ErrorInfo."\r\n");
		}
        @fputs($this->flogs, "[".date("Y-m-d H:i:s")."] INFO: Step 3 has been done.\r\n");
    }
}