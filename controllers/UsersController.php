<?php

namespace app\controllers;

use app\models\Usages;
use app\models\UserNewPass;
use app\models\Users;
use app\models\UsersSearch;
use app\models\UsersUsages;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class UsersController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => [
                    'users',
                    'users-show',
                    'users-change-pass',
                    'users-edit',
                    'users-usages',
                    'usages-edit',
                    'users-new',
                    'users-delete',

                ],
                'rules' => [
                    [
                        'allow'         => true,
                        'actions'       => [
                            'users',
                            'users-show',
                            'users-change-pass',
                            'users-edit',
                            'users-usages',
                            'usages-edit',
                            'users-new',
                            'users-delete',
                        ],
                        'roles'         => ['@'],
                        'matchCallback' => function () {
                            return Yii::$app->user->identity->getIsAdmin();
                        },
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionUsers() {
        $usersSearch = new UsersSearch();
        $users = $usersSearch->search(Yii::$app->request->get());

        return $this->render('users', ['users' => $users, 'usersSearch' => $usersSearch]);
    }

    public function actionUsersShow($id) {
        $usersShow = Users::find()->where(['users_id' => (int) $id])->asArray()->one();

        return $this->render('usersShow', ['usersShow' => $usersShow]);
    }

    public function actionUsersChangePass($id) {
        if (
            Yii::$app->request->getIsPost() &&
            (Yii::$app->request->post()['UserNewPass']['password'] ==
                Yii::$app->request->post()['UserNewPass']['password2'])
        ) {
            $user = Users::findOne($id);
            $user->users_password = md5(Yii::$app->request->post()['UserNewPass']['password'].$user->users_login);
            $user->save();

            Yii::$app->session->setFlash(
                'success',
                "Пароль пользователя с логином " . $user->users_login . " изменён."
            );
        }

        $userNewPass = new UserNewPass();
        $user = Users::findOne($id);

        return $this->render('usersChangePass', ['userNewPass' => $userNewPass, 'user' => $user]);
    }

    public function actionUsersEdit($id) {
        $userEdit = Users::findOne($id);

        if ($userEdit->load(Yii::$app->request->post()) && $userEdit->validate()) {
            $userEdit->save();

            Yii::$app->session->setFlash(
                'success', 'Данные успешно сохранены.');
        }

        return $this->render('userEdit', ['userEdit' => $userEdit]);
    }

    public function actionUsersUsages($id) {
        $usersUsages = Usages::find()
            ->select('*')
            ->leftJoin('users_usages', 'usages_id = uu_usages_id and uu_users_id = ' . $id)
            ->orderBy('usages_name')
            ->asArray()->all();
        $user = Users::findOne($id);

        return $this->render('usersUsages', ['usersUsages' => $usersUsages, 'user' => $user]);
    }

    public function actionUsagesEdit($id) {
        if (!empty(Yii::$app->request->post()['usages'])) {
            Yii::$app
                ->db
                ->createCommand()
                ->delete('users_usages', ['uu_users_id' => $id])
                ->execute();

            $usagesId = array_keys(Yii::$app->request->post()['usages']);
            foreach ($usagesId as $v) {
                $model = new UsersUsages(); // creating new instance of model
                $model->uu_users_id = (int)$id;
                $model->uu_usages_id = (int)$v;
                $model->save();
            }
        } else {
            Yii::$app
                ->db
                ->createCommand()
                ->delete('users_usages', ['uu_users_id' => $id])
                ->execute();
        }

        Yii::$app->session->setFlash('success', 'Данные успешно сохранены.');

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionUsersNew() {
        $newUser = new Users();

        if ($newUser->load(Yii::$app->request->post()) && $newUser->validate()) {

            if (!$newUser->isLoginUnique()) {
                Yii::$app->session->setFlash(
                    'danger', "Пользователь с логином «" . $newUser->users_login . "» уже есть!");

                return $this->render('usersNew', ['newUser' => $newUser]);
            }
            $newUser->users_password = md5($newUser->users_password.$newUser->users_login);
            Yii::$app->session->setFlash(
                'success', "Новый пользователь с логином «" .
                $newUser->users_login .
                '» добавлен.'
            );
            $newUser->save();
        }
        $newUser = new Users();

        return $this->render('usersNew', ['newUser' => $newUser]);
    }

    public function actionUsersDelete($id) {
        $model = Users::findOne($id);
        $model->delete();

        Yii::$app->session->setFlash(
            'success', "Пользователь с логином " .
            $model->users_login .
            ' удалён.');

        return $this->redirect(['/users']);
    }
}
