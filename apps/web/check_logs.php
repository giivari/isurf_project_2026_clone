
<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/common/config/bootstrap.php';
require __DIR__ . '/console/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    require __DIR__ . '/console/config/main.php',
    require __DIR__ . '/console/config/main-local.php'
);

$application = new yii\console\Application($config);

$db = Yii::$app->db;
$logs = $db->createCommand("SELECT * FROM {{%sensor_log}} ORDER BY created_at DESC LIMIT 5")->queryAll();

echo "Total Data in sensor_log: " . count($logs) . "
";
foreach($logs as $log) {
    print_r($log);
}
