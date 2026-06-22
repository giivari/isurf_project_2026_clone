<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Json;

/**
 * Controller for exporting and importing database tables to/from JSON.
 */
class DataController extends Controller
{
    private $tables = ['user', 'sensor_log'];
    private $dataPath = '@app/../data';

    /**
     * Exports defined tables to JSON files.
     */
    public function actionExport()
    {
        $path = Yii::getAlias($this->dataPath);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        foreach ($this->tables as $table) {
            try {
                $data = Yii::$app->db->createCommand("SELECT * FROM {{" . $table . "}}")->queryAll();
                $file = $path . '/' . $table . '.json';
                file_put_contents($file, Json::encode($data, JSON_PRETTY_PRINT));
                $this->stdout("Exported table $table to $file\n");
            } catch (\Exception $e) {
                $this->stderr("Error exporting $table: " . $e->getMessage() . "\n");
            }
        }
    }

    /**
     * Imports defined tables from JSON files.
     */
    public function actionImport()
    {
        $path = Yii::getAlias($this->dataPath);

        foreach ($this->tables as $table) {
            $file = $path . '/' . $table . '.json';
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $data = Json::decode($content);
                
                if (!empty($data)) {
                    try {
                        // Disable foreign key checks
                        Yii::$app->db->createCommand("SET FOREIGN_KEY_CHECKS=0;")->execute();
                        
                        // Truncate table
                        Yii::$app->db->createCommand()->truncateTable('{{%' . $table . '}}')->execute();
                        
                        // Insert data
                        Yii::$app->db->createCommand()->batchInsert('{{%' . $table . '}}', array_keys($data[0]), $data)->execute();
                        
                        // Enable foreign key checks
                        Yii::$app->db->createCommand("SET FOREIGN_KEY_CHECKS=1;")->execute();
                        
                        $this->stdout("Imported table $table from $file\n");
                    } catch (\Exception $e) {
                        $this->stderr("Error importing $table: " . $e->getMessage() . "\n");
                    }
                }
            } else {
                $this->stdout("File $file not found, skipping $table\n");
            }
        }
    }
}
