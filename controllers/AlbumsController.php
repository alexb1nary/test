<?php

namespace app\controllers;

use app\models\Albums;
use app\models\AlbumsSearch;
use app\models\Tracks;
use app\models\TracksSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\web\Controller;

class AlbumsController extends Controller
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

    public function actionAlbums()
    {
        $albumsSearch = new AlbumsSearch();
        $albums = $albumsSearch->search(Yii::$app->request->get());

        return $this->render('albums', ['albums' => $albums, 'albumsSearch' => $albumsSearch]);
    }

    public function actionAlbumInfo($id) {
        $albumInfo = Albums::find()
            ->select('*')
            ->leftJoin('artists', 'albums.album_artist_id = artists.artist_id')
            ->leftJoin('albtypes', 'albums.album_albtype = albtypes.albtype_id')
            ->where(['album_id' => (int) $id])->asArray()->one();

        $query = Tracks::find()
            ->select('*')
            ->joinWith(['albumTracks', 'artists'])
            ->joinWith(['artists'])
            ->where(['at_album_id' => (int) $id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 200,
            ]
        ]);

        $dataProvider->sort->attributes['artists.artist_name'] = [
            'asc' => ['artists.artist_name' => SORT_ASC],
            'desc' => ['artists.artist_name' => SORT_DESC],
        ];

        return $this->render('albumInfo', ['albumInfo' => $albumInfo, 'albumTracks' => $dataProvider]);
    }
}