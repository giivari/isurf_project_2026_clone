<?php

use yii\db\Migration;

class m240622_000002_create_sensor_log extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%sensor_log}}', [
            'id' => $this->primaryKey(),
            'device_id' => $this->string(50)->notNull(),
            'temperature' => $this->float()->null(),
            'humidity' => $this->float()->null(),
            'tds' => $this->float()->null(),
            'ph' => $this->float()->null(),
            'created_at' => $this->integer()->notNull(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%sensor_log}}');
    }
}
