<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use common\models\SensorLog;

class MqttWorkerController extends Controller
{
    /**
     * Jalankan worker ini di background: php yii mqtt-worker/run
     */
    public function actionRun()
    {
        $server   = '40ce76f98591453e962925f524ea06fa.s1.eu.hivemq.cloud';
        $port     = 8883;
        $clientId = 'isurf-backend-' . uniqid();
        $topic    = 'isurf/device/sensor';

        echo "Menghubungkan ke MQTT Broker $server:$port...\n";

        try {
            $mqtt = new MqttClient($server, $port, $clientId);
            
            $settings = (new ConnectionSettings())
                ->setUsername('isurf')
                ->setPassword('testIsurf123')
                ->setKeepAliveInterval(60)
                ->setConnectTimeout(10)
                ->setUseTls(true)
                ->setTlsVerifyPeer(false);

            $mqtt->connect($settings, true);
            echo "Berhasil terhubung. Subscribing ke topik: $topic\n";

            $mqtt->subscribe($topic, function ($topic, $message) {
                echo sprintf("[%s] Menerima data: %s\n", date('Y-m-d H:i:s'), $message);
                
                $data = json_decode($message, true);
                if ($data) {
                    $log = new SensorLog();
                    $log->device_id = $data['device_id'] ?? 'ESP32_MAIN_01';
                    $log->temperature = isset($data['temperature']) ? (float)$data['temperature'] : null;
                    $log->humidity = isset($data['humidity']) ? (float)$data['humidity'] : null;
                    $log->tds = isset($data['tds']) ? (float)$data['tds'] : null;
                    $log->ph = isset($data['ph']) ? (float)$data['ph'] : null;
                    $log->created_at = time();
                    
                    if ($log->save()) {
                        echo "  -> Berhasil disimpan ke database (ID: {$log->id})\n";
                    } else {
                        echo "  -> Gagal menyimpan: " . json_encode($log->errors) . "\n";
                    }
                }
            }, 0);

            $mqtt->loop(true);
            $mqtt->disconnect();

        } catch (\Exception $e) {
            echo "Worker Error: " . $e->getMessage() . "\n";
        }
    }
}
