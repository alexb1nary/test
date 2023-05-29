<?php

/** @var yii\web\View $this */

use yii\bootstrap4\LinkPager;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Документы РСП';
if (isset($songShow)) {
    $this->params['breadcrumbs'][] = [
        'label' => 'Документы РСП',
        'url' => ['site/docsrao'],
    ];
    $this->params['breadcrumbs'][] = '#' . $songShow['song_id'];
} else {
    $this->params['breadcrumbs'][] = $this->title;
}

echo GridView::widget([
        'dataProvider' => $docsrsp,
        'filterModel' => $docsrspSearch,
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
                'label' => 'ID',
                'attribute' => 'cdoc_id',
                'headerOptions' => ['style' => 'width: 6%; text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
            ],
            [
                'label' => 'КЛИЕНТ',
                'attribute' => 'clients.client_name',
                'headerOptions' => ['style' => 'width: 35%; text-align: center']
            ],
            [
                'label' => 'НАИМЕНОВАНИЕ',
                'attribute' => 'cdoc_name',
                'headerOptions' => ['style' => 'width: 35%; text-align: center'],
            ],
            [
                'label' => 'ТИП',
                'attribute' => 'cdoc_ext',
                'headerOptions' => ['style' => 'width: 2%; text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
            ],
            [
                'label' => 'ЗАГРУЖЕН',
                'attribute' => 'cdoc_loaded',
                'headerOptions' => ['style' => 'width: 6%; text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
                'value' => function($data) {
                    return date("Y-m-d", strtotime($data->cdoc_loaded));
                }
            ],
            [
                'label' => 'ДАТА',
                'attribute' => 'cdoc_date',
                'headerOptions' => ['style' => 'width: 7%; text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
                'value' => function($data) {
                    return date("Y-m-d", strtotime($data->cdoc_date));
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'headerOptions' => ['style' => 'text-align: center'],
                'contentOptions' => ['style' => 'width: 3%; text-align: center'],
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a(
                            '<i class="fas fa-download"></i>',
                            '/clidocs/rsp/' . $model->cdoc_id . '/' . $model->cdoc_name . '.' . $model->cdoc_ext);

                    },
                ],
                'header' => '',
                'buttonOptions' => ['align' => 'center']
            ],
        ]
    ]);
