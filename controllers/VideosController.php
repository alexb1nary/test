<?php

namespace app\controllers;

use app\models\UsersUsages;
use app\models\Videos;
use app\models\VideosSearch;
use Yii;
use yii\data\ArrayDataProvider;
use yii\web\Controller;

class VideosController extends Controller
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


    public function actionVideos()
    {
        $videosSearch = new VideosSearch();
        $videos = $videosSearch->search(Yii::$app->request->get());

        return $this->render('videos', ['videos' => $videos, 'videosSearch' => $videosSearch]);
    }

    public function actionVideoInfo($id) {
        $videoInfo = Videos::find()
            ->select('*')
            ->leftJoin('songs', 'videos.video_song_id = songs.songs_id')
            ->leftJoin('artists', 'videos.video_artist_id = artists.artist_id')
            ->where(['video_id' => (int) $id])->asArray()->one();

        $videoAuthors = Videos::getVideoAuthors($id);
        $videoAuthors = new ArrayDataProvider([
            'allModels' => $videoAuthors,
            'pagination' => [
                'pageSize' => 200,
            ],
        ]);

        if (!Yii::$app->user->getIsGuest()) {
            $videoParts = Videos::getVideoParts($id);
            $mec = 0;
            $syn = 0;
            $pub = 0;

            foreach ($videoParts as $k => $v) {
                if ($mec < $v['part_mec']) $mec = $v['part_mec'];
                if ($syn < $v['part_syn']) $syn = $v['part_syn'];
                if ($pub < $v['part_pub']) $pub = $v['part_pub'];
            }

            foreach ($videoParts as $k => $v) {
                $videoParts[$k]['part_mec'] = $mec;
                $videoParts[$k]['part_syn'] = $syn;
                $videoParts[$k]['part_pub'] = $pub;
            }
            $videoParts = new ArrayDataProvider([
                'allModels' => $videoParts,
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
                foreach ($videoParts->allModels as $k => $v) {
                    if (!in_array($v['usage_id'], $userUsages)) {
                        unset($videoParts->allModels[$k]);
                    }
                }

                $videoParts->allModels = array_values($videoParts->allModels);
            }

            return $this->render('videoInfo', [
                'videoInfo' => $videoInfo,
                'videoParts' => $videoParts,
                'videoAuthors' => $videoAuthors
            ]);
        }

        return $this->render('videoInfo', ['videoInfo' => $videoInfo, 'videoAuthors' => $videoAuthors]);
    }
}
