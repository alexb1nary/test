<?php

/** @var yii\web\View $this */

use yii\bootstrap4\LinkPager;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$this->params['breadcrumbs'][] = 'Альбомы';

echo GridView::widget([
        'dataProvider' => $albums,
        'filterModel' => $albumsSearch,
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
                'headerOptions' => ['style' => 'width: 1%; text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
            ],
            [
                'label' => 'ID',
                'attribute' => 'album_id',
                'headerOptions' => ['style' => 'width: 6%; text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
                'value' => function($data) {
                    return $data->album_id ?: '';
                }
            ],
            [
                'label' => 'НАЗВАНИЕ',
                'attribute' => 'album_name',
                'headerOptions' => ['style' => 'width: 40%; text-align: center'],
                'value' => function($data) {
                    return $data->album_name ?: '';
                }
            ],
            [
                'label' => 'АРТИСТ',
                'attribute' => 'artists.artist_name',
                'headerOptions' => ['style' => 'width: 40%; text-align: center'],
                'value' => function($data) {
                    //dd($data);
                    return isset($data->artists->artist_name) ? $data->artists->artist_name : '';
                }
            ],
            [
                'label' => 'ДАТА ВЫХОДА',
                'attribute' => 'album_release',
                'headerOptions' => ['style' => 'width: 8%; text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
                'value' => function($data) {
                    return $data->album_release ?: '';
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'headerOptions' => ['style' => 'text-align: center'],
                'contentOptions' => ['style' => 'width: 8%; text-align: center'],
                'buttons'=> [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', '/albums/id/' . $key);

                    },
                ],
                'header' => '',
                'buttonOptions' => ['align' => 'center']
            ],
        ]
    ]);
