<?php

namespace app\controllers;

use app\models\Tracks;
use app\models\TracksSearch;
use app\models\UsersUsages;
use Yii;
use yii\data\ArrayDataProvider;
use yii\web\Controller;

class TracksController extends Controller
{
    public function behaviors()
    {
        return [];
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

    public function actionTracks()
    {
        $tracksSearch = new TracksSearch();
        $tracks = $tracksSearch->search(Yii::$app->request->get());

        return $this->render('tracks', ['tracks' => $tracks, 'tracksSearch' => $tracksSearch]);
    }

    public function actionTrackInfo($id) {
        $trackParams = Tracks::getTrackParams($id);
        $trackAuthors = Tracks::getTrackAuthors($id);
        $trackAuthors = new ArrayDataProvider([
            'allModels' => $trackAuthors,
            'pagination' => [
                'pageSize' => 200,
            ],
        ]);

        if (!Yii::$app->user->getIsGuest()) {
            $trackParts = Tracks::getTrackParts($id);
            $trackParts = new ArrayDataProvider([
                'allModels' => $trackParts,
                'pagination' => [
                    'pageSize' => 200,
                ],
            ]);

            //usages
            $userUsages = UsersUsages::find()
                ->select('uu_usages_id')
                ->where(['uu_users_id' => Yii::$app->user->identity->users_id])->asArray()->all();
            $userUsages = array_column($userUsages, 'uu_usages_id');
            if ($userUsages) {
                foreach ($trackParts->allModels as $k => $v) {
                    if (!in_array($v['usage_id'], $userUsages)) {
                        unset($trackParts->allModels[$k]);
                    }
                }

                $trackParts->allModels = array_values($trackParts->allModels);
            }

            return $this->render('trackInfo', [
                'trackParams' => $trackParams,
                'trackParts' => $trackParts,
                'trackAuthors' => $trackAuthors
            ]);
        }

        return $this->render('trackInfo', ['trackParams' => $trackParams, 'trackAuthors' => $trackAuthors]);
    }
}