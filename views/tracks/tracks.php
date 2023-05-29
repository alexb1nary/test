<?php

/**
 * @var yii\web\View $this
 * @var \yii\data\ActiveDataProvider $tracks
 * @var \app\models\TracksSearch $tracksSearch
 */

use yii\bootstrap4\LinkPager;
use yii\grid\GridView;
use yii\helpers\Html;

$this->params['breadcrumbs'][] = 'Фонограммы';

echo GridView::widget([
        'dataProvider' => $tracks,
        'filterModel' => $tracksSearch,
        'tableOptions' => ['class' => 'table table-striped table-bordered hoverTable'],
        'pager' =>
            [
                'class' => LinkPager::class,
                'firstPageLabel' => 'начало',
                'lastPageLabel' => 'конец',
                'options' => [
                    'class' => 'float-right'
                ]
            ],
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['style' => 'width: 3%; text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
            ],
            [
                'label' => 'CODE',
                'attribute' => 'track_code',
                'headerOptions' => ['style' => 'width: 6%; text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
                'value' => function($data) {
                    return $data->track_code ?: '';
                }
            ],
            [
                'label' => 'НАЗВАНИЕ',
                'attribute' => 'track_name',
                'headerOptions' => ['style' => 'width: 25%; text-align: center'],
                'value' => function($data) {
                    return $data->track_name ?: '';
                }
            ],
            [
                'label' => 'АРТИСТ',
                'attribute' => 'artists.artist_name',
                'headerOptions' => ['style' => 'width: 25%; text-align: center'],
                'value' => function($data) {
                    return isset($data->artists->artist_name) ? $data->artists->artist_name : '';
                }
            ],
            [
                'label' => 'АВТОРЫ',
                'attribute' => 'songs.songs_authors',
                'headerOptions' => ['style' => 'width: 28%; text-align: center'],
                'value' => function($data) {
                    return isset($data->songs->songs_authors) ? $data->songs->songs_authors : '';
                }
            ],
            [
                'label' => 'ISRC',
                'attribute' => 'track_isrc',
                'headerOptions' => ['style' => 'width: 8%; text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
                'value' => function($data) {
                    return $data->track_isrc ?: '';
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'headerOptions' => ['style' => 'width: 5%; text-align: center'],
                'contentOptions' => ['style' => 'width: 10%; text-align: center'],
                'buttons'=> [
                    'view' => function ($url, $model, $key) {

                        return Html::a('<i class="fas fa-eye"></i>', '/tracks/id/' . $model->track_id);

                    },
                ],
                'header' => '',
                'buttonOptions' => ['align' => 'center']
            ],
        ]
    ]);