<?php

namespace app\models;

use Couchbase\SearchFacetResult;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class DocsraoSearch extends Clidocs
{
    public function attributes()
    {
        // делаем поле зависимости доступным для поиска
        return array_merge(parent::attributes(), ['clients.client_name']);
    }

    public function rules()
    {
        return [
            [['cdoc_id'], 'integer'],
            [['clients.client_name', 'cdoc_name', 'cdoc_ext', 'cdoc_loaded', 'cdoc_date'], 'string'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Clidocs::find()->where(['cdoc_onsite' => 1]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ]
        ]);

        $query->joinWith(['clients']);

        $dataProvider->sort->attributes['clients.client_name'] = [
            'asc' => ['clients.client_name' => SORT_ASC],
            'desc' => ['clients.client_name' => SORT_DESC],
        ];

        // загружаем данные формы поиска и производим валидацию
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'cdoc_id', $this->cdoc_id]);
        $query->andFilterWhere(['like', 'clients.client_name', $this->getAttribute('clients.client_name')]);
        $query->andFilterWhere(['like', 'cdoc_name', $this->cdoc_name]);
        $query->andFilterWhere(['like', 'cdoc_ext', $this->cdoc_ext]);
        $query->andFilterWhere(['like', 'cdoc_loaded', $this->cdoc_loaded]);
        $query->andFilterWhere(['like', 'cdoc_date', $this->cdoc_date]);

        return $dataProvider;
    }
}