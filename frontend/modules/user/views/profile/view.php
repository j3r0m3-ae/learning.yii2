<?php

/* @var \yii\web\View $this */
/* @var \frontend\models\User $user */

use yii\helpers\Html;

?>
<?php $this->title = Html::encode($user->username); ?>
<h3><?= Html::encode($user->username) ?></h3>
<hr>
<h4><b>Обо мне:</b></h4>
<p><?= Html::encode($user->about) ?></p>