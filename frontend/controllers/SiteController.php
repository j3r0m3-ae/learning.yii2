<?php
namespace frontend\controllers;

use yii\helpers\ArrayHelper;
use yii\web\Controller;
use Yii;
use yii\httpclient\Client;

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
        $apteki = Yii::$app->db->createCommand("SELECT id, name, city, address, x_coordinate, y_coordinate, region_id, final_address FROM apteki_acc")->queryAll();
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
                    $newApt['a'] = $apteka['final_address'] ?? $apteka['city'].', '.$apteka['address'];
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
            $jsonReg = json_encode($result[$key], JSON_UNESCAPED_UNICODE);

            file_put_contents('json/jsonAcc'.$region['id'].'.txt', $jsonReg);
        }

        $json = json_encode($result, JSON_UNESCAPED_UNICODE);

        return $this->renderPartial('json', [
            'json' => $json
        ]);
    }

    public function actionJsonbm()
    {
        $regions = Yii::$app->db->createCommand('SELECT * FROM region')->queryAll();
        $apteki = Yii::$app->db->createCommand("SELECT id, name, city, address, x_coordinate, y_coordinate, region_id, final_address FROM apteki_bm")->queryAll();
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
                    $newApt['a'] = $apteka['final_address'] ?? $apteka['city'].', '.$apteka['address'];
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
        file_put_contents('json/jsonBm.txt', $json);

        return $this->renderPartial('json', [
            'json' => $json
        ]);
    }

    public function actionSend()
    {
        $apteki = Yii::$app->db->createCommand("SELECT id, city, address, region_id FROM apteki_acc WHERE address IS NOT NULL AND x_coordinate IS NULL")->queryAll();
        $regions = Yii::$app->db->createCommand("SELECT id, t FROM region")->queryAll();
        $apikey = '3fb96219-c77a-4c81-bdb8-d6fa448a02c2';
        foreach ($apteki as $apteka) {
            foreach ($regions as $region) {
                if ($region['id'] == $apteka['region_id']) {
                    $address = $region['t'].', '.$apteka['city'].', '.$apteka['address'];
                    break;
                }
            }
//            $coordinats = $apteka['x_coordinate']." ".$apteka['y_coordinate'];
            $client = new Client();

            $response = $client->createRequest()
                ->setMethod('post')
                ->setUrl('https://geocode-maps.yandex.ru/1.x/')
                ->setData([
                    'apikey' => $apikey,
                    'geocode' => $address,
//                    'geocode' => $coordinats,
                ])
                ->send();
//            $data = ArrayHelper::getValue($response->getData(), 'GeoObjectCollection.featureMember', []);
            $data = $response->getData()['GeoObjectCollection'];
//            $count = ArrayHelper::getValue($data, 'metaDataProperty.GeocoderResponseMetaData.found');
            $count = (int) $data['metaDataProperty']['GeocoderResponseMetaData']['found'];
            if ($count == 1) {
                $result = array_shift($data['featureMember']);
                $coor = explode(' ', $result['Point']['pos']);
                $x_coor = array_shift($coor);
                $y_coor = array_shift($coor);
//                $newAddress = explode(', ', $result['metaDataProperty']['GeocoderMetaData']['text']);
//                array_shift($newAddress);
//                $finalAddress = implode(', ', $newAddress);
                $sql = Yii::$app->db->createCommand()
                    ->update('apteki_acc', [
                        'x_coordinate' => $x_coor,
                        'y_coordinate' => $y_coor,
//                        'final_address' => $finalAddress
                    ], "id = ".$apteka['id']);
                $sql->execute();
            }
        }

        return $this->renderPartial('send', [

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
