<?php

class MainController extends Controller
{
    /**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',   // allow ADMIN users to perform 'index' actions
				'actions'=>array('index','profile'),
				'roles'=>array('ADMIN'),
			),
			array('deny',    // deny all users
				'users'=>array('*'),
			),
		);
	}
    
	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
        // устанавливаем заголовок страницы
        $this->pageTitle = Yii::app()->name.' - '.Yii::t('main', 'Project description');
        Yii::app()->clientScript->registerCoreScript('jquery');
        Yii::app()->clientScript->registerCoreScript('bootstrap');
        
        $this->render('index');
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error = Yii::app()->errorHandler->error)
		{
            // view page title
            $this->pageTitle = Yii::t('main', 'Error').' / '.Yii::app()->name;
            
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}
}