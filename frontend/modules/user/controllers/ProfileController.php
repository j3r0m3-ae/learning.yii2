<?php

namespace frontend\modules\user\controllers;

use Yii;
use yii\web\Controller;
use frontend\models\User;
use yii\web\NotFoundHttpException;

class ProfileController extends Controller
{

    public function actionIndex()
    {
        $users = User::find()->all();

        return $this->render('index', [
            'users' => $users,
        ]);
    }

    public function actionView($slug)
    {
        $user = $this->getUserBySlug($slug);

        return $this->render('view', [
            'user' => $user,
        ]);
    }

    public function actionSubscribe($id)
    {
        if(Yii::$app->user->isGuest) {
            return $this->redirect(['/user/default/login']);
        }

        $currentUser = Yii::$app->user->identity;
        $user = $this->getUserById($id);

        $currentUser->subscribe($user);

        return $this->redirect(['/user/profile/view', 'slug' => $user->getSlug()]);
    }

    public function actionUnsubscribe($id)
    {
        if(Yii::$app->user->isGuest) {
            return $this->redirect(['/user/default/login']);
        }

        $currentUser = Yii::$app->user->identity;
        $user = $this->getUserById($id);

        $currentUser->unsubscribe($user);

        return $this->redirect(['/user/profile/view', 'slug' => $user->getSlug()]);
    }
    /**
     * @param string $slug
     * @return User
     * @throws NotFoundHttpException
     */
    private function getUserBySlug($slug)
    {
        if($user = User::find()->where(['id' => $slug])->orWhere(['nickname' => $slug])->one()) {
            return $user;
        }

        throw new NotFoundHttpException('Пользователь не найден');
    }

    /**
     * @param integer $id
     * @return User
     * @throws NotFoundHttpException
     */
    private function getUserById($id)
    {
        if($user = User::findOne($id)) {
            return $user;
        }

        throw new NotFoundHttpException('Пользователь не найден');
    }
}