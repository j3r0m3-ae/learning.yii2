<?php
namespace frontend\controllers;

use yii\web\Controller;
use Yii;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionApteki()
    {
        $file = file_get_contents('json.txt');
        $json = json_decode($file);

        return $this->render('apteki', [
            'json' => $json,
        ]);
    }

    public function actionJson()
    {
        $res = Yii::$app->db->createCommand('SELECT * FROM region')->queryAll();
        foreach ($res as $key => $elem) {
            $elem['id'] = (int) $elem['id'];
            $elem['s'] = (bool) $elem['s'];
            $result[$key]['region'] = $elem;
            if ($elem['s']) {
                $resNew = Yii::$app->db->createCommand('SELECT * FROM subways')->queryAll();
                foreach ($resNew as $keyNew => $elemNew) {
                    $resNew[$keyNew]['id'] = (int) $elemNew['id'];
                    $resNew[$keyNew]['la'] = (float) $elemNew['la'];
                    $resNew[$keyNew]['lo'] = (float) $elemNew['lo'];
                }
                $result[$key]['subways'] = $resNew;
            }
        }


        $json = json_encode($result, JSON_UNESCAPED_UNICODE);

        return $this->renderPartial('json', [
            'json' => $json
        ]);
    }
}
