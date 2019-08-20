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
        $apteki = Yii::$app->db->createCommand("SELECT * FROM apteki_acc WHERE address IS NOT NULL AND x_coordinate IS NOT NULL")->queryAll();
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

//            file_put_contents('json/jsonAcc'.$region['id'].'.txt', $jsonReg);
        }

        $json = json_encode($result, JSON_UNESCAPED_UNICODE);

        return $this->renderPartial('json', [
            'json' => $json
        ]);
    }

    public function actionJsonbm()
    {
        $regions = Yii::$app->db->createCommand('SELECT * FROM region')->queryAll();
        $apteki = Yii::$app->db->createCommand("SELECT * FROM apteki_bm WHERE address IS NOT NULL AND x_coordinate IS NOT NULL")->queryAll();
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
//        file_put_contents('json/jsonBm.txt', $json);

        return $this->renderPartial('json', [
            'json' => $json
        ]);
    }

    public function actionSend()
    {
        $apteki = Yii::$app->db->createCommand("SELECT * FROM apteki_acc WHERE final_address IS NULL AND address IS NOT NULL AND x_coordinate IS NOT NULL")->queryAll();
        die;
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
//            $address = $apteka['region_name'].', '.$apteka['city'].', '.$apteka['address'];
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
//                $coor = explode(' ', $result['Point']['pos']);
//                $x_coor = array_shift($coor);
//                $y_coor = array_shift($coor);
                $newAddress = explode(', ', $result['metaDataProperty']['GeocoderMetaData']['text']);
                array_shift($newAddress);
                $finalAddress = implode(', ', $newAddress);
                $sql = Yii::$app->db->createCommand()
                    ->update('apteki_acc', [
//                        'x_coordinate' => $x_coor,
//                        'y_coordinate' => $y_coor,
                        'final_address' => $finalAddress
                    ], "id = ".$apteka['id']);
                $sql->execute();
            }
        }

        return $this->renderPartial('send', [

        ]);
    }

    public function actionTest()
    {
        $apikey = '3fb96219-c77a-4c81-bdb8-d6fa448a02c2';

        $sql = 'SELECT BM.id AS bm_id, BM.name, BM.city, BM.address, BM.x_coordinate, BM.y_coordinate, R.id AS reg_id, R.t, R.tr, R.bl
                FROM apteki_bm AS BM
                JOIN region AS R
                ON BM.region_id = R.id AND R.id NOT IN (1, 2)';
//        $sql = 'SELECT ACC.id AS acc_id, ACC.name, ACC.city, ACC.address, ACC.x_coordinate, ACC.y_coordinate, R.id AS reg_id, R.t, R.tr, R.bl
//                FROM apteki_acc AS ACC
//                JOIN region AS R
//                ON ACC.region_id = R.id AND R.id NOT IN (1, 2)';

        $apteki = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($apteki as $key => $apteka) {
            $tr_new = explode(',', $apteka['tr']);
            $bl_new = explode(',', $apteka['bl']);

            $newApt['id'] = (int) $apteka['bm_id'];
            $newApt['name'] = $apteka['name'];
            $newApt['region_name'] = $apteka['t'];
            $newApt['city'] = $apteka['city'];
            $newApt['address'] = $apteka['address'];
            $newApt['y_coordinate'] = (float) $apteka['y_coordinate'];
            $newApt['x_coordinate'] = (float) $apteka['x_coordinate'];
            $newApt['y_coor']['max'] = (float) array_shift($tr_new);
            $newApt['y_coor']['min'] = (float) array_shift($bl_new);
            $newApt['x_coor']['max'] = (float) array_shift($tr_new);
            $newApt['x_coor']['min'] = (float) array_shift($bl_new);

            if (!($newApt['y_coordinate'] <= $newApt['y_coor']['max'] && $newApt['y_coordinate'] >= $newApt['y_coor']['min']
            && $newApt['x_coordinate'] <= $newApt['x_coor']['max'] && $newApt['x_coordinate'] >= $newApt['x_coor']['min'])) {
                $apteki_final[] = $newApt;
            }
        }

//        foreach ($apteki_final as $apteka_final) {
//            $address = $apteka_final['region_name'].', '.$apteka_final['city'].', '.$apteka_final['address'];
//            $client = new Client();
//
//            $response = $client->createRequest()
//                ->setMethod('post')
//                ->setUrl('https://geocode-maps.yandex.ru/1.x/')
//                ->setData([
//                    'apikey' => $apikey,
//                    'geocode' => $address,
//                ])
//                ->send();
//            $data = $response->getData()['GeoObjectCollection'];
//            $count = (int) $data['metaDataProperty']['GeocoderResponseMetaData']['found'];
//            if ($count == 1) {
//                $result = array_shift($data['featureMember']);
//                $coor = explode(' ', $result['Point']['pos']);
//                $x_coor = array_shift($coor);
//                $y_coor = array_shift($coor);
//                $sql = Yii::$app->db->createCommand()
//                    ->update('apteki_bm', [
//                        'x_coordinate' => $x_coor,
//                        'y_coordinate' => $y_coor,
//                    ], "id = ".$apteka_final['id']);
//                $sql->execute();
//            }
//        }

        return $this->renderPartial('apteki', [
            'json' => $apteki_final,
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
