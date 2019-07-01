<?php

namespace frontend\models;

use Yii;

class Test
{
    public static function getNewsList()
    {
        $sql = "SELECT * FROM news WHERE status = 1";
        return Yii::$app->db->createCommand($sql)->queryAll();
    }
}