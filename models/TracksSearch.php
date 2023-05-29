<?php

namespace app\models;

use Couchbase\SearchFacetResult;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class TracksSearch extends Tracks
{
    public function attributes()
    {
        // делаем поле зависимости доступным для поиска
        return array_merge(parent::attributes(), ['artists.artist_name', 'songs.songs_authors']);
    }

    public function rules()
    {
        return [
            [['track_id'], 'integer'],
            [['track_name', 'track_code'], 'string'],
            [['artists.artist_name', 'songs.songs_authors'], 'string'],
            [['track_isrc'], 'string'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Tracks::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ]
        ]);

        $query->joinWith(['artists']);
        $query->joinWith(['songs']);

        $dataProvider->sort->attributes['artists.artist_name'] = [
            'asc' => ['artists.artist_name' => SORT_ASC],
            'desc' => ['artists.artist_name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['songs.songs_authors'] = [
            'asc' => ['songs.songs_authors' => SORT_ASC],
            'desc' => ['songs.songs_authors' => SORT_DESC],
        ];

        // загружаем данные формы поиска и производим валидацию
        if (!($this->load($params) && $this->validate())) {
            self::getDb()->cache(function () use ($dataProvider) {
                $dataProvider->prepare();
            }, 3600);

            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'track_id', $this->track_id]);
        $query->andFilterWhere(['like', 'track_code', $this->track_code]);
        $query->andFilterWhere(['like', 'track_name', $this->track_name]);
        $query->andFilterWhere(['like', 'track_isrc', $this->track_isrc]);
        $query->andFilterWhere(['LIKE', 'artists.artist_name', $this->getAttribute('artists.artist_name')]);
        $query->andFilterWhere(['LIKE', 'songs.songs_authors', $this->getAttribute('songs.songs_authors')]);

        self::getDb()->cache(function () use ($dataProvider) {
            $dataProvider->prepare();
        }, 3600);

        return $dataProvider;
    }
}