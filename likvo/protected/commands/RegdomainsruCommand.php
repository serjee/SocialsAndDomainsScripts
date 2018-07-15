<?php
/**
 * RegdomainsruCommand class - working with list of pending delete domain
 * Yii Console Command for find the best deleted domain
 * Copyright (c) 2013 Bashkov S.S.
 * 
 * USAGE:
 * ./yiic regdomainsru
 * 
 * @author Bashkov Sergey <ser.bsh@gmail.com>
 * @copyright Copyright (c) 2013 Bashkov S.S.
 * @version 1.0, 2013-07-04
 */

class RegdomainsruCommand extends CConsoleCommand
{
	public function run($args)
	{
        // Создаем файл для записи лога
        $uploaddir = dirname(Yii::app()->basePath).'/upload/';
        $flogs = @fopen($uploaddir."logs_regdomainsru","w");
        @fputs($flogs, "[".date("Y-m-d H:i:s")."] INFO: Process started.\r\n");
        
        // find orders for domains registration
        $countreg = Interdomains::model()->count("DATE (date)=:date AND interception=:interception AND success=:success", array(":date"=>date("Y-m-d"),":interception"=>1,":success"=>0));
        if ($countreg < 1)
        {
            @fputs($flogs, "[".date("Y-m-d H:i:s")."] INFO: Cannot find domains for the registred!\r\n");
            @fputs($flogs, "[".date("Y-m-d H:i:s")."] INFO: Process done!\r\n");
            @fclose($flogs);
            return; /// exit if not found domains
        }
        
        // set start and end times
        $cur_time = time(); // start time
        $end_time = $cur_time+(Yii::app()->params['tryRegMin']*60); // end time (+20min)
        
        // counter for request
        $counterRequest = 0;
        
        // while current time < end time
        while ($cur_time < $end_time)
        {
            // get all domain which need registration
            $regdomains = Interdomains::model()->findAll("DATE (date)=:date AND interception=:interception AND success=:success", array(":date"=>date("Y-m-d"),":interception"=>1,":success"=>0));
            foreach($regdomains as $domain)
            {
                $regruApi = new ApiRegru();
                
                // TODO: сделать возможность работы под разными аккаунтами + решить баги работы через прокси
                // if up to 1199 then use own ip, else use proxy
                if ($counterRequest > 1199)
                {
                    $response = $regruApi->createDomainByProfile($domain->domain,'RU.PP','SER(RU)',Yii::app()->params['proxyapi']);
                }
                else
                {
                    $response = $regruApi->createDomainByProfile($domain->domain);
                }                
                
                // if success than update status
                if ($response['result'] === 'success')
                {
                    // set registred status for domain
                    $domain_reg = Interdomains::model()->findByPk($domain->id);
                    $domain_reg->success = '1';
                    if($domain_reg->save())
                    {
                        // E-mail data
                        $message .= '<tr>';
                        $message .= '<td valign="top"><p>'.$domain->domain.'</p></td>';
                        $message .= '<td valign="top"><p>'.$domain->cy.'</p></td>';
                        $message .= '<td valign="top"><p>'.$domain->pr.'</p></td>';
                        $message .= '<td valign="top"><p>'.$domain->dmoz.'</p></td>';
                        $message .= '<td valign="top"><p>'.$domain->dmoz_count.'</p></td>';
                        $message .= '<td valign="top"><p>'.$domain->wa.'</p></td>';
                        $message .= '<td valign="top"><p>'.$domain->wa_count.'</p></td>';
                        $message .= '<td valign="top"><p>'.$domain->glue_cy.'</p></td>';
                        $message .= '<td valign="top"><p>'.$domain->glue_pr.'</p></td>';
                        $message .= '<td valign="top"><p>'.$domain->yaca.'</p></td>';
                        $message .= '</tr>';
                    }
                    else
                    {
                        @fputs($flogs, "[".date("Y-m-d H:i:s")."] ERROR: Cannot save domain '".$domain->domain."'\r\n");
                    }
                }
                else
                {
                    @fputs($flogs, "[".date("Y-m-d H:i:s")."] ERROR: Cannot reg domain '".$domain->domain."'. ".$response['error_text'].".\r\n");
                }
                
                $counterRequest++;
            }
            
            // sleep before retrying
            sleep(Yii::app()->params['sleepSec']);
            $cur_time = time(); // update current time
            
        } // end while
        
        // SEND EMAIL NOTICE
        
        if ($message === '')
        {
            @fputs($flogs, "[".date("Y-m-d H:i:s")."] INFO: Nothing domain has been registred!\r\n");
            @fputs($flogs, "[".date("Y-m-d H:i:s")."] INFO: Process done!\r\n");
            @fclose($flogs);
            
            return; // exit if message is empty
        }
        
        //get template 'domains' from /themes/default/views/mail
        $mail = new YiiMailer('domains', array('message'=>$message, 'title'=>'Зарегистрированные домены', 'date'=>date("d-m-Y")));
        
        //render HTML mail, layout is set from config file or with $mail->setLayout('layoutName')
        $mail->render();
        
        //set properties as usually with PHPMailer
        $mail->From = Yii::app()->params['emailFrom'];
        $mail->FromName = Yii::app()->params['fromNameEmail'];
        $mail->Subject = 'Зарегистрированные домены';
        $mail->AddAddress(Yii::app()->params['emailTo']);
        
        //send
        if ($mail->Send())
        {
            $mail->ClearAddresses();
            @fputs($flogs, "[".date("Y-m-d H:i:s")."] INFO: Mail sent successfuly!\r\n");
		}
        else
        {
            @fputs($flogs, "[".date("Y-m-d H:i:s")."] ERROR: Error while sending email: ".$mail->ErrorInfo."\r\n");
		}
        
        @fputs($flogs, "[".date("Y-m-d H:i:s")."] INFO: Process done!\r\n");
        @fclose($flogs);
	}
}