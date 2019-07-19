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
     * @param integer $id User id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $user = User::findOne($id);
        if(!$user) {
            throw new NotFoundHttpException('Пользователь не найден');
        }

        return $this->render('view', [
            'user' => $user,
        ]);
    }
}