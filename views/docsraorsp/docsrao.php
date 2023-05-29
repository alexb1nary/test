<?php

/** @var yii\web\View $this */

use yii\bootstrap4\LinkPager;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Документы РАО';
if (isset($songShow)) {
    $this->params['breadcrumbs'][] = [
        'label' => 'Документы РАО',
        'url' => ['site/docsrao'],
    ];
    $this->params['breadcrumbs'][] = '#' . $songShow['song_id'];
} else {
    $this->params['breadcrumbs'][] = $this->title;
}
?>

<?php
if (isset($songShow)) {
    ?>
    <div class="container">
        <div class="row">
            <h2 >Произведение</h2>
            <table class="table table-bordered success">
                <thead>
                <tr>
                    <th style="width: 25%">ID</th>
                    <td><?=$songShow['song_id']?></td>
                </tr>
                <tr>
                    <th class="info">Название</th>
                    <td><?=$songShow['song_name']?></td>
                </tr>
                <tr>
                    <th class="info">Авторы</th>
                    <td><?=$songShow['song_authors']?></td>
                </tr>
                <tr>
                    <th class="info">Исполнитель</th>
                    <td><?=$songShow['song_artist']?></td>
                </tr>
                <tr>
                    <th class="info">Тема</th>
                    <td></td>
                </tr>
                </thead>
            </table>
        </div>
    </div>


    <?php
} else {
    echo GridView::widget([
        'dataProvider' => $docsrao,
        'filterModel' => $docsraoSearch,
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
                                '/clidocs/rao/' . $model->cdoc_id . '/' . $model->cdoc_name . '.' . $model->cdoc_ext);

                    },
                ],
                'header' => '',
                'buttonOptions' => ['align' => 'center']
            ],
        ]
    ]);
}
