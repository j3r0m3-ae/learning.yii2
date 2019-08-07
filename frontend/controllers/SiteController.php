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

    public function actionJsonacc()
    {
        $regions = Yii::$app->db->createCommand('SELECT * FROM region')->queryAll();
        $apteki = Yii::$app->db->createCommand("SELECT id, name, city, address, x_coordinate, y_coordinate, region_id FROM apteki_acc")->queryAll();
        $subways = Yii::$app->db->createCommand('SELECT * FROM subways')->queryAll();

        foreach ($regions as $key => $region) {
            $region['id'] = (int) $region['id'];
            $region['s'] = (bool) $region['s'];
            $result[$key]['region'] = $region;
            if ($region['s']) {
                foreach ($subways as $keyNew => $elemNew) {
                    $subways[$keyNew]['id'] = (int) $elemNew['id'];
                    $subways[$keyNew]['la'] = (float) $elemNew['la'];
                    $subways[$keyNew]['lo'] = (float) $elemNew['lo'];
                }
                $result[$key]['subways'] = $subways;
            }

            foreach ($apteki as $apteka) {
                $apteka['region_id'] = (int) $apteka['region_id'];
                if ($region['id'] == $apteka['region_id']) {
                    $newApt['id'] = (int) $apteka['id'];
                    $newApt['t'] = $apteka['name'];
                    $newApt['a'] = $apteka['city'].', '.$apteka['address'];
                    $newApt['la'] = (float) $apteka['y_coordinate'];
                    $newApt['lo'] = (float) $apteka['x_coordinate'];
                    if ($region['s']) {
                        $newApt['stations'] = [];
                        foreach ($subways as $keySub => $subway) {
                            $dist = $this->distance($newApt['la'], $newApt['lo'], $subway['la'], $subway['lo']);
                            if ($dist < 1) {
                                $station['t'] = $subway['t'];
                                $station['hex'] = $subway['hex'];
                                $station['d'] = (string) round($dist, 1);
                                $newApt['stations'][] = $station;
                            }
                        }
                    }
                    $result[$key]['stores'][] = $newApt;
                    unset($newApt);
                }
            }
        }

        $json = json_encode($result, JSON_UNESCAPED_UNICODE);

        return $this->renderPartial('json', [
            'json' => $json
        ]);
    }

    public function actionJsonbm()
    {
        $regions = Yii::$app->db->createCommand('SELECT * FROM region')->queryAll();
        $apteki = Yii::$app->db->createCommand("SELECT id, name, city, address, x_coordinate, y_coordinate, region_id FROM apteki_bm")->queryAll();
        $subways = Yii::$app->db->createCommand('SELECT * FROM subways')->queryAll();

        foreach ($regions as $key => $region) {
            $region['id'] = (int) $region['id'];
            $region['s'] = (bool) $region['s'];
            $result[$key]['region'] = $region;
            if ($region['s']) {
                foreach ($subways as $keyNew => $elemNew) {
                    $subways[$keyNew]['id'] = (int) $elemNew['id'];
                    $subways[$keyNew]['la'] = (float) $elemNew['la'];
                    $subways[$keyNew]['lo'] = (float) $elemNew['lo'];
                }
                $result[$key]['subways'] = $subways;
            }

            foreach ($apteki as $apteka) {
                $apteka['region_id'] = (int) $apteka['region_id'];
                if ($region['id'] == $apteka['region_id']) {
                    $newApt['id'] = (int) $apteka['id'];
                    $newApt['t'] = $apteka['name'];
                    $newApt['a'] = $apteka['city'].', '.$apteka['address'];
                    $newApt['la'] = (float) $apteka['y_coordinate'];
                    $newApt['lo'] = (float) $apteka['x_coordinate'];
                    if ($region['s']) {
                        $newApt['stations'] = [];
                        foreach ($subways as $keySub => $subway) {
                            $dist = $this->distance($newApt['la'], $newApt['lo'], $subway['la'], $subway['lo']);
                            if ($dist < 1) {
                                $station['t'] = $subway['t'];
                                $station['hex'] = $subway['hex'];
                                $station['d'] = (string) round($dist, 1);
                                $newApt['stations'][] = $station;
                            }
                        }
                    }
                    $result[$key]['stores'][] = $newApt;
                    unset($newApt);
                }
            }
        }

        $json = json_encode($result, JSON_UNESCAPED_UNICODE);

        return $this->renderPartial('json', [
            'json' => $json
        ]);
    }

    protected function distance($lat1, $lon1, $lat2, $lon2) {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);

        return $dist * 60 * 1.1515 * 1.609344;
    }
}
