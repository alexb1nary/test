<?php

/** @var yii\web\View $this */

use yii\bootstrap4\LinkPager;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$this->title = 'Пользователи';

//dd($this->params);

if (isset($trackShow)) {
    $this->params['breadcrumbs'][] = [
        'label' => 'Пользователи',
        'url' => ['users/users'],
    ];
    $this->params['breadcrumbs'][] = '#' . $trackShow['track_code'];
} else {
    $this->params['breadcrumbs'][] = $this->title;
}
echo '<form action="/users/new" method="post">
        <div style="text-align: right">
            <button type="submit" style="background-color: #7209b7" class="btn btn-primary btn-add-user">
            ДОБАВИТЬ
            </button>
        </div>
      </form>';
echo GridView::widget([
        'dataProvider' => $users,
        'filterModel' => $usersSearch,
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
                'label' => 'ЛОГИН',
                'attribute' => 'users_login',
                'headerOptions' => ['style' => 'width: 8%; text-align: center'],
                'value' => function($data) {
                    return $data->users_login ?: '';
                }
            ],
            [
                'label' => 'ПОЧТА',
                'attribute' => 'users_email',
                'headerOptions' => ['style' => 'width: 25%;text-align: center'],
                'value' => function($data) {
                    return $data->users_email ?: '';
                }
            ],
            [
                'label' => 'ФИО',
                'attribute' => 'users_fio',
                'headerOptions' => ['style' => 'width: 35%;text-align: center'],
                'value' => function($data) {
                    //dd($data);
                    return $data->users_fio ?: '';
                }
            ],
            [
                'label' => 'АКТИВНЫЙ',
                'attribute' => 'users_is_active',
                'headerOptions' => ['style' => 'width: 5%; text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
                'value' => function($data) {
                    return $data->users_is_active ? 'Да' : 'Нет' ;
                }
            ],
            [
                'label' => 'ВИЗИТОВ',
                'attribute' => 'users_visits_sum',
                'headerOptions' => ['style' => 'width: 8%; text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
                'value' => function($data) {
                    return $data->users_visits_sum;
                }
            ],
            [
                'label' => 'ПОСЛЕДНИЙ ВИЗИТ',
                'attribute' => 'users_last_visit',
                'headerOptions' => ['style' => 'width: 8%; text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
                'value' => function($data) {
                    return $data->users_last_visit == '' ? 'никогда' : $data->users_last_visit;
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}{usages}{pass}{edit}{del}',
                'headerOptions' => ['style' => 'width: 8%; text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
                'buttons'=> [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fa fa-eye"></i>', '/users/id/' . $key, [
                            'style' => 'margin-right: 5px',
                            'data-toggle'=>'tooltip',
                            'title'=>'Смотреть',
                            ]);

                    },
                    'usages' => function ($url, $model, $key) {
                        return Html::a('<i class="fa fa-table"></i>', '/users/usages/id/' . $key, [
                            'style' => 'margin-right: 5px',
                            'data-toggle'=>'tooltip',
                            'title'=>'Ограничить по условиям использования',
                        ]);

                    },
                    'pass' => function ($url, $model, $key) {
                        return Html::a('<i class="fa fa-key"></i>', '/users/pass/id/' . $key, [
                            'style' => 'margin-right: 5px',
                            'data-toggle'=>'tooltip',
                            'title'=>'Изменить пароль',
                        ]);

                    },
                    'edit' => function ($url, $model, $key) {
                        return Html::a('<i class="fa fa-pen"></i>', '/users/edit/id/' . $key, [
                            'style' => 'margin-right: 5px',
                            'data-toggle'=>'tooltip',
                            'title'=>'Изменить',
                        ]);

                    },
                    'del' => function ($url, $model, $key) {
                        return Html::a('<i class="fa fa-trash-alt"></i>', '/users/del/id/' . $key, [
                            'class' => 'user-delete',
                            'data-toggle'=>'tooltip',
                            'title'=>'Удалить',
                        ]);

                    },
                ],
                'header' => 'ДЕЙСТВИЯ',
                'buttonOptions' => ['align' => 'center']
            ],
        ]
    ]);
?>