<?php

namespace frontend\modules\user\controllers;

use yii\web\Controller;
use frontend\models\User;
use yii\web\NotFoundHttpException;

class ProfileController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $users = User::find()->all();

        return $this->render('index', [
            'users' => $users,
        ]);
    }

    /**
     * @param string $slug
     * @return string

     */
    public function actionView($slug)
    {
        $user = $this->findUserBySlug($slug);

        return $this->render('view', [
            'user' => $user,
        ]);
    }

    /**
     * @param string $slug
     * @return User
     * @throws NotFoundHttpException
     */
    public function findUserBySlug($slug)
    {
        if($user = User::find()->where(['id' => $slug])->orWhere(['nickname' => $slug])->one()) {
            return $user;
        }

        throw new NotFoundHttpException('Пользователь не найден');
    }
}