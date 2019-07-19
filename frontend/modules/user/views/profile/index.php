<?php

/* @var \yii\web\View $this */
/* @var \frontend\models\User[] $users */

use yii\helpers\Url;
use yii\helpers\Html;

?>
<?php $this->title = 'Профили'; ?>
<h1>Профили</h1>
    <hr>
<?php if(!empty($users)): ?>
    <?php foreach($users as $user): ?>
        <a href="<?= Url::to(['profile/view', 'slug' => $user->getSlug()])?>"><?= Html::encode($user->username) ?></a>
        <hr>
    <?php endforeach; ?>
<?php endif; ?>