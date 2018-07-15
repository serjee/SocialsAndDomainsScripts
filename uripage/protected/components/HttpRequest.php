<?php

class HttpRequest extends CHttpRequest
{
    public $noCsrfValidationRoutes = array();

    protected function normalizeRequest()
    {
        parent::normalizeRequest();

        $route = Yii::app()->getUrlManager()->parseUrl($this);
        if($this->enableCsrfValidation)
        {
            foreach($this->noCsrfValidationRoutes as $cr)
            {
                if(preg_match('#'.$cr.'#', $route))
                {
                    Yii::app()->detachEventHandler('onBeginRequest', array($this,'validateCsrfToken'));
                    Yii::trace('Route "'.$route.' passed without CSRF validation');

                    break; // found first route and break
                }
            }
        }
    }
}