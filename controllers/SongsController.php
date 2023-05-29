<?php

namespace app\controllers;

use app\models\Songs;
use app\models\SongsSearch;
use app\models\UsersUsages;
use Yii;
use yii\data\ArrayDataProvider;
use yii\web\Controller;

class SongsController extends Controller
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

    public function actionSongs()
    {
        $songsSearch = new SongsSearch();
        $songs = $songsSearch->search(Yii::$app->request->get());

        return $this->render('songs', ['songs' => $songs, 'songsSearch' => $songsSearch]);
    }

    public function actionSongInfo($id) {
        $songParams = Songs::find()
            ->where(['songs_id' => (int) $id])->asArray()->one();
        $songAuthors = Songs::getSongAuthors($id);
        $songAuthors = new ArrayDataProvider([
            'allModels' => $songAuthors,
            'pagination' => [
                'pageSize' => 200,
            ],
        ]);

        if (!Yii::$app->user->getIsGuest()) {
            $songParts = Songs::getSongParts($id);
            $mec = 0;
            $syn = 0;
            $pub = 0;

            foreach ($songParts as $k => $v) {
                if ($mec < $v['part_mec']) $mec = $v['part_mec'];
                if ($syn < $v['part_syn']) $syn = $v['part_syn'];
                if ($pub < $v['part_pub']) $pub = $v['part_pub'];
            }

            foreach ($songParts as $k => $v) {
                $songParts[$k]['part_mec'] = $mec;
                $songParts[$k]['part_syn'] = $syn;
                $songParts[$k]['part_pub'] = $pub;
            }
            $songParts = new ArrayDataProvider([
                'allModels' => $songParts,
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
                foreach ($songParts->allModels as $k => $v) {
                    if (!in_array($v['usage_id'], $userUsages)) {
                        unset($songParts->allModels[$k]);
                    }
                }

                $songParts->allModels = array_values($songParts->allModels);
            }

            return $this->render('songInfo', [
                'songParams' => $songParams,
                'songParts' => $songParts,
                'songAuthors' => $songAuthors
            ]);
        }

        return $this->render('songInfo', ['songParams' => $songParams, 'songAuthors' => $songAuthors]);
    }
}