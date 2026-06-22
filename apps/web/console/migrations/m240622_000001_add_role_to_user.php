<?php

use yii\db\Migration;

class m240622_000001_add_role_to_user extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'full_name', $this->string(255)->null());
        $this->addColumn('{{%user}}', 'role', $this->string(50)->notNull()->defaultValue('user'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'role');
        $this->dropColumn('{{%user}}', 'full_name');
    }
}
