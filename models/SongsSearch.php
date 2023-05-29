<?php

namespace app\models;

use Couchbase\SearchFacetResult;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class SongsSearch extends Songs
{
    /*public function attributes()
    {
        // делаем поле зависимости доступным для поиска
        //return array_merge(parent::attributes(), ['artists.artist_name']);
        return [];
    }*/

    public function rules()
    {
        return [
            [['songs_id'], 'integer'],
            [['songs_name'], 'string'],
            [['songs_code'], 'string'],
            [['songs_authors', 'songs_artist'], 'string'],
            [['songs_album'], 'string'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Songs::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ]
        ]);

        // загружаем данные формы поиска и производим валидацию
        if (!($this->load($params) && $this->validate())) {
            self::getDb()->cache(function () use ($dataProvider) {
                $dataProvider->prepare();
            }, 3600);

            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'songs_id', $this->songs_id]);
        $query->andFilterWhere(['like', 'songs_code', $this->songs_code]);
        $query->andFilterWhere(['like', 'songs_name', $this->songs_name]);
        $query->andFilterWhere(['like', 'songs_authors', $this->songs_authors]);
        $query->andFilterWhere(['like', 'songs_artist', $this->songs_artist]);
        $query->andFilterWhere(['like', 'songs_album', $this->songs_album]);

        self::getDb()->cache(function () use ($dataProvider) {
            $dataProvider->prepare();
        }, 3600);

        return $dataProvider;
    }
}