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

    public function actionArticles()
    {
        $articles = Yii::$app->db->createCommand("SELECT id, subtitle, content FROM articles LIMIT 100")->queryAll();
        foreach ($articles as $article) {
            $updatedSubtitle = $this->update($article['subtitle']);
            $updatedContent = $this->update($article['content']);
            Yii::$app->db->createCommand('UPDATE articles SET subtitle = \''.$updatedSubtitle.'\', content = \''.$updatedContent.'\' WHERE id = '.$article['id'])->execute();
        }


        return $this->render('articles', [
            'articles' => $articles
        ]);
    }

    private function update($text)
    {
        $text = preg_replace("#\t+#", "", $text);
        $text = preg_replace("#\n+#", "\n", $text);
        $text = preg_replace("#^\n#", "", $text);
        $text = preg_replace('#\'#', '"', $text);
        $text = preg_replace('#<div.+?>#', '', $text);
        $text = preg_replace('#</div>#', '', $text);
        $text = preg_replace('#&nbsp;#', ' ', $text);
        $text = preg_replace('# class=".+?""#', '', $text);
        $text = preg_replace('#&laquo;#', '<<', $text);
        $text = preg_replace('#&raquo;#', '>>', $text);
        $text = preg_replace('#&mdash;#', '-', $text);
        $text = preg_replace('#&ndash;#', '-', $text);
        $text = preg_replace('#<span>(.+?)</span>#', '$1', $text);
        $text = preg_replace('#<p>(.+?)</p>#s', '$1', $text);
        $text = preg_replace('#<!--.+?-->#s', '', $text);
        $text = preg_replace('#<style.+?/style>#s', '', $text);
        $text = preg_replace('#<img.+?src=""(.+?)"".+?>#', '<img src="https://www.nestlebaby.ru$1">', $text);
        $text = preg_replace('#<iframe.+?src=""(.+?)"".+?>#', '<video src="$1">', $text);
        $text = preg_replace('#</iframe>#', '', $text);
        $text = preg_replace('#<a.+?href=""(.+?)"".+?>#', '<link src="https://www.nestlebaby.ru$1">', $text);
        $text = preg_replace('#</a>#', '</link>', $text);

        return $text;
    }

    public function actionJsonacc()
    {
//        ini_set('precision', -1);
        $regions = Yii::$app->db->createCommand('SELECT * FROM region WHERE id = 4')->queryAll();
        $apteki = Yii::$app->db->createCommand("SELECT * FROM 1_apteki_acc")->queryAll();
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
                    $newApt = [];
                }
            }
            $jsonReg = json_encode($result[$key], JSON_UNESCAPED_UNICODE);

            file_put_contents('./json/jsonAcc'.$region['id'].'.txt', $jsonReg);
        }

        $json = json_encode($result, JSON_UNESCAPED_UNICODE);

        return $this->renderPartial('json', [
            'json' => $json
        ]);
    }

    public function actionJsonbm()
    {
        $regions = Yii::$app->db->createCommand('SELECT * FROM region')->queryAll();
        $apteki = Yii::$app->db->createCommand("SELECT * FROM 1_apteki_bm")->queryAll();
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
                    $newApt = [];
                }
            }
        }

        $json = json_encode($result, JSON_UNESCAPED_UNICODE);
//        file_put_contents('./json/jsonBm.txt', $json);

        return $this->renderPartial('json', [
            'json' => $json
        ]);
    }

    public function actionSend()
    {
        $sql = "SELECT BM.id, R.t region_name, BM.city, BM.address
              FROM 1_apteki_bm BM
              JOIN region R
              ON BM.region_id = R.id
              WHERE BM.final_address IS NULL AND BM.region_id BETWEEN 76 AND 82";

        $apteki = Yii::$app->db->createCommand($sql)->queryAll();

        $apikey = '3fb96219-c77a-4c81-bdb8-d6fa448a02c2';

        foreach ($apteki as $apteka) {
//            $coordinats = $apteka['x_coordinate']." ".$apteka['y_coordinate'];
            $address = $apteka['region_name'].', '.$apteka['city'].', '.$apteka['address'];

            $client = new Client();
            $response = $client->createRequest()
                ->setMethod('get')
                ->setUrl('https://geocode-maps.yandex.ru/1.x/')
                ->setData([
                    'apikey' => $apikey,
                    'geocode' => $address,
//                    'geocode' => $coordinats,
                ])
                ->send();
            $data = ArrayHelper::getValue($response->getData(), 'GeoObjectCollection', []);
            $count = (int) ArrayHelper::getValue($data, 'metaDataProperty.GeocoderResponseMetaData.found');

            if ($count == 1) {
                $result = array_shift($data['featureMember']);
//                $coor = explode(' ', $result['Point']['pos']);
//                $x_coor = array_shift($coor);
//                $y_coor = array_shift($coor);
                $responseAddress = ArrayHelper::getValue($result, 'metaDataProperty.GeocoderMetaData.text');
                $finalAddress = substr($responseAddress, strpos($responseAddress, ' ') + 1);
                $sql = Yii::$app->db->createCommand()
                    ->update('1_apteki_bm', [
//                        'x_coordinate' => $x_coor,
//                        'y_coordinate' => $y_coor,
                        'final_address' => $finalAddress
                    ], "id = ".$apteka['id']);
//                $sql->execute();
            }
        }

        return $this->renderPartial('send', [

        ]);
    }

    public function actionTest()
    {
        $sql = 'SELECT BM.id AS apt_id, BM.name, BM.city, BM.address, BM.x_coordinate, BM.y_coordinate, R.id AS reg_id, R.t, R.tr, R.bl
                FROM 1_apteki_bm AS BM
                JOIN region AS R
                ON BM.region_id = R.id
                WHERE R.id NOT IN (1, 2)';
//        $sql = 'SELECT ACC.id AS apt_id, ACC.name, ACC.city, ACC.address, ACC.x_coordinate, ACC.y_coordinate, R.id AS reg_id, R.t, R.tr, R.bl
//                FROM 1_apteki_acc AS ACC
//                JOIN region AS R
//                ON ACC.region_id = R.id
//                WHERE R.id NOT IN (1, 2)';

        $apteki = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($apteki as $key => $apteka) {
            $tr = explode(',', $apteka['tr']);
            $bl = explode(',', $apteka['bl']);

            $newApt['id'] = (int) $apteka['apt_id'];
            $newApt['name'] = $apteka['name'];
            $newApt['region_name'] = $apteka['t'];
            $newApt['city'] = $apteka['city'];
            $newApt['address'] = $apteka['address'];
            $newApt['y_coordinate'] = (float) $apteka['y_coordinate'];
            $newApt['x_coordinate'] = (float) $apteka['x_coordinate'];
            $newApt['y_coor']['max'] = (float) array_shift($tr);
            $newApt['y_coor']['min'] = (float) array_shift($bl);
            $newApt['x_coor']['max'] = (float) array_shift($tr);
            $newApt['x_coor']['min'] = (float) array_shift($bl);

            if (!($newApt['y_coordinate'] <= $newApt['y_coor']['max'] && $newApt['y_coordinate'] >= $newApt['y_coor']['min']
            && $newApt['x_coordinate'] <= $newApt['x_coor']['max'] && $newApt['x_coordinate'] >= $newApt['x_coor']['min'])) {
                $apteki_final[] = $newApt;
            }
            $newApt = [];
        }

//        $apikey = '3fb96219-c77a-4c81-bdb8-d6fa448a02c2';
//
//        foreach ($apteki_final as $apteka_final) {
//            $address = $apteka_final['region_name'] . ', ' . $apteka_final['city'] . ', ' . $apteka_final['address'];
//
//            $client = new Client();
//
//            $response = $client->createRequest()
//                ->setMethod('get')
//                ->setUrl('https://geocode-maps.yandex.ru/1.x/')
//                ->setData([
//                    'apikey' => $apikey,
//                    'geocode' => $address,
//                ])
//                ->send();
//
//            $data = ArrayHelper::getValue($response->getData(), 'GeoObjectCollection', []);
//            $count = (int)ArrayHelper::getValue($data, 'metaDataProperty.GeocoderResponseMetaData.found');
//
//            if ($count == 1) {
//                $result = array_shift($data['featureMember']);
//                $responseAddress = ArrayHelper::getValue($result, 'metaDataProperty.GeocoderMetaData.text');
//                $finalAddress = substr($responseAddress, strpos($responseAddress, ' ') + 1);
//                $coor = explode(' ', $result['Point']['pos']);
//                $x_coor = array_shift($coor);
//                $y_coor = array_shift($coor);
//                $sql = Yii::$app->db->createCommand()
//                    ->update('1_apteki_acc', [
//                        'x_coordinate' => $x_coor,
//                        'y_coordinate' => $y_coor,
//                    ], "id = " . $apteka_final['id']);
//                $sql->execute();
//            }
//        }


        return $this->renderPartial('apteki', [
            'apteki' => $apteki_final,
        ]);
    }

    public function actionDecode()
    {
        $string = file_get_contents('./json/test1.txt');
        $array = json_decode($string);

        return $this->renderPartial('apteki', [
            'apteki' => $array
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
