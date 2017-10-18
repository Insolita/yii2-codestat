<?php

use yii\db\Migration;

class m170412_230002_migration extends Migration
{
    
    public function safeUp()
    {
        $tableOptions = '';
        if ($this->db->driverName == 'mysql') {
            $tableOptions = 'ENGINE=INNODB';
        }
        $this->createTable(
            '{{%config}}',
            [
                'code' => $this->string(100)->notNull(),
                'name' => $this->string(100)->notNull(),
                'section' => $this->string(100)->notNull()->defaultValue('Common'),
                'hint' => $this->string(255)->notNull()->defaultValue(''),
                'order' => $this->smallInteger(16)->null()->defaultValue(1),
                'value' => $this->text(),
                'field' => $this->string(100)->notNull()->defaultValue('input'),
                '[[fieldData]]' => $this->text(),
                'rules' => $this->text(),
            ],
            $tableOptions
        );
        $this->createIndex('config_code_key', '{{%config}}', 'code', true);
    }
    
    public function safeDown()
    {
        $this->dropIndex('config_code_key', '{{%config}}');
        $this->dropTable('{{%config}}');
    }
}
