<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%sensor_log}}".
 *
 * @property int $id
 * @property string $device_id
 * @property float|null $temperature
 * @property float|null $humidity
 * @property float|null $tds
 * @property float|null $ph
 * @property int $created_at
 */
class SensorLog extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sensor_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['device_id', 'created_at'], 'required'],
            [['temperature', 'humidity', 'tds', 'ph'], 'number'],
            [['created_at'], 'integer'],
            [['device_id'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'device_id' => 'Device ID',
            'temperature' => 'Temperature',
            'humidity' => 'Humidity',
            'tds' => 'TDS',
            'ph' => 'pH',
            'created_at' => 'Created At',
        ];
    }
}
