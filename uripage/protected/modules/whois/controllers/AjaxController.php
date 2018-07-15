<?php

/**
 * AjaxController - контроллер WhoIs
 */
class AjaxController extends Controller
{
    /**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'ajaxOnly + domaincheck',
            'ajaxOnly + whoisinfo',
		);
	}
    
    /**
     * Check domain and get data about
     */
	public function actionDomainCheck()
	{
        $data = array();
        
        // если запрос асинхронный, то нам нужно отдать только данные
        if(Yii::app()->request->isAjaxRequest)
        {
            $wd = new Whois( Yii::app()->request->getPost('whoisinput') );
            if ($wd->isValid())
            {
                $data = $wd->getDomainsData();
                if (count($data) === 1)
                {
                    if($data[0]['errormessage'])
                    {
                        $error = $data[0]['errormessage'];
                    }
                }
            }
            else
            {
                $error = $wd->getErrorText();
            }
        }
        
        $this->renderPartial('_ajaxDomainCheckResult', array('data'=>$data, 'error'=>$error), false, true);
	}
    
    /**
     * Check domain and get data about
     */
	public function actionWhoisInfo()
	{
        // если запрос асинхронный, то нам нужно отдать только данные
        if(Yii::app()->request->isAjaxRequest)
        {
            $wd = new Whois( Yii::app()->request->getPost('domain') );
            if ($wd->isValid())
            {
                $data = $wd->getDomainsData();
                if($data[0]['errormessage'])
                {
                    echo '<p>'.CHtml::encode($data[0]['errormessage']).'</p>';
                    Yii::app()->end();
                }
                else
                {
                    echo '<p><pre>'.CHtml::encode($data[0]['domaininfo']).'</pre></p>';                    
                    Yii::app()->end();
                }
            }
            else
            {
                echo '<p>'.CHtml::encode($wd->getErrorText()).'</p>';
                Yii::app()->end();
            }
        }
	}
}