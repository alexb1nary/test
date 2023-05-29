<?php

namespace app\controllers;

use app\models\DocsrspSearch;
use app\models\DocsraoSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class DocsraorspController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only'  => [
                    'docsrao',
                    'docsrsp',

                ],
                'rules' => [
                    [
                        'allow'         => true,
                        'actions'       => [
                            'docsrao',
                        ],
                        'roles'         => ['@'],
                        'matchCallback' => function () {
                            return Yii::$app->user->identity->getCanDocsRao();
                        }
                    ],
                    [
                        'allow'         => true,
                        'actions'       => [
                            'docsrsp',
                        ],
                        'roles'         => ['@'],
                        'matchCallback' => function () {
                            return Yii::$app->user->identity->getCanDocsRsp();
                        },
                        'denyCallback'  => function ($rule, $action) {
                            Yii::$app->session->setFlash('error', 'This section is only for registered users.');
                            Yii::$app->user->loginRequired();
                        },
                    ],
                ],
            ],
        ];
    }

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
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionDocsrao() {
        $docsraoSearch = new DocsraoSearch();
        $docsrao = $docsraoSearch->search(Yii::$app->request->get());

        return $this->render('docsrao', ['docsrao' => $docsrao, 'docsraoSearch' => $docsraoSearch]);
    }

    public function actionDocsrsp() {
        $docsrspSearch = new DocsrspSearch();
        $docsrsp = $docsrspSearch->search(Yii::$app->request->get());

        return $this->render('docsrsp', ['docsrsp' => $docsrsp, 'docsrspSearch' => $docsrspSearch]);
    }

}
