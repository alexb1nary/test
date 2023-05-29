<?php

/**
 * @var yii\web\View $this
 * @var \app\models\Songs $songs
 * @var \app\models\SongsSearch $songsSearch
 */

use yii\bootstrap4\LinkPager;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Произведения';
$this->params['breadcrumbs'][] = $this->title;

echo GridView::widget([
    'dataProvider' => $songs,
    'filterModel' => $songsSearch,
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
            'headerOptions' => ['style' => 'width: 5%; text-align: center'],
            'contentOptions' => ['style' => 'text-align: center'],
        ],
        [
            'label' => 'CODE',
            'attribute' => 'songs_code',
            'headerOptions' => ['style' => 'width: 6%; text-align: center'],
            'contentOptions' => ['style' => 'text-align: center'],
        ],
        [
            'label' => 'НАЗВАНИЕ',
            'attribute' => 'songs_name',
            'headerOptions' => ['style' => 'width: 25%; text-align: center'],
            'value' => function($data) {
                return $data->songs_name ?: '';
            }
        ],
        [
            'label' => 'АВТОРЫ',
            'attribute' => 'songs_authors',
            'headerOptions' => ['style' => 'width: 25%; text-align: center'],
            'value' => function($data) {
                return $data->songs_authors ?: '';
            }
        ],
        [
            'label' => 'ИСПОЛНИТЕЛИ',
            'attribute' => 'songs_artist',
            'headerOptions' => ['style' => 'width: 25%; text-align: center'],
            'value' => function($data) {
                return $data->songs_artist ?: '';
            }
        ],
        [
            'label' => 'АЛЬБОМ',
            'attribute' => 'songs_album',
            'headerOptions' => ['style' => 'width: 10%; text-align: center'],
            'value' => function($data) {
                return $data->songs_album ?: '';
            }
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view}',
            'headerOptions' => ['style' => 'text-align: center'],
            'contentOptions' => ['style' => 'width: 5%; text-align: center'],
            'buttons' => [
                'view' => function ($url, $model, $key) {
                    return Html::a('<i class="fas fa-eye"></i>', '/songs/id/' . $model->songs_id);

                },
            ],
            'header' => '',
            'buttonOptions' => ['align' => 'center']
        ],
    ]
]);
