<?php

namespace app\models;

use Couchbase\SearchFacetResult;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class AlbumsSearch extends Albums
{
    public function attributes()
    {
        // делаем поле зависимости доступным для поиска
        return array_merge(parent::attributes(), ['artists.artist_name']);
    }

    public function rules()
    {
        return [
            [['album_name', 'artists.artist_name', 'album_release', 'album_code'], 'string'],
            [['album_id'], 'integer'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Albums::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ]
        ]);

        $query->joinWith(['artists']);

        $dataProvider->sort->attributes['artists.artist_name'] = [
            'asc' => ['artists.artist_name' => SORT_ASC],
            'desc' => ['artists.artist_name' => SORT_DESC],
        ];

        // загружаем данные формы поиска и производим валидацию
        if (!($this->load($params) && $this->validate())) {
            self::getDb()->cache(function () use ($dataProvider) {
                $dataProvider->prepare();
            }, 3600);

            return $dataProvider;
        }

        // изменяем запрос добавляя в его фильтрацию
        $query->andFilterWhere(['like', 'album_code', $this->album_code]);
        $query->andFilterWhere(['like', 'album_id', $this->album_id]);
        $query->andFilterWhere(['like', 'album_name', $this->album_name]);
        $query->andFilterWhere(['like', 'album_release', $this->album_release]);
        $query->andFilterWhere(['LIKE', 'artists.artist_name', $this->getAttribute('artists.artist_name')]);

        self::getDb()->cache(function () use ($dataProvider) {
            $dataProvider->prepare();
        }, 3600);


        return $dataProvider;
    }
}