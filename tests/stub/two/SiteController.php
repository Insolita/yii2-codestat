<?php

namespace tests\stub\two;


use Yii;
use yii\base\Module;
use yii\web\Controller;

class SiteController extends Controller
{
    
    public function __construct($id, Module $module, array $config = [])
    {
        parent::__construct($id, $module, $config);
    }
    
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    
    public function beforeAction($action)
    {
        Yii::info($action->id, __METHOD__);
        return parent::beforeAction($action);
    }
    
    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
    
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        
        return $this->goHome();
    }
    
}
