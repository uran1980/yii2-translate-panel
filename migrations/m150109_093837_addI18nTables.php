<?php

use yii\base\InvalidConfigException;
use yii\db\Migration;
use yii\db\Schema;

class m150109_093837_addI18nTables extends Migration
{
    /**
     * @return bool|void
     * @throws InvalidConfigException
     */
    public function safeUp()
    {
        $i18n = Yii::$app->getI18n();

        if (!isset($i18n->sourceMessageTable) || !isset($i18n->messageTable)) {
            throw new InvalidConfigException('You should configure i18n component');
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($i18n->sourceMessageTable, [
            'id'        => Schema::TYPE_PK,
            'hash'      => Schema::TYPE_STRING  . '(32) NOT NULL DEFAULT ""',
            'category'  => Schema::TYPE_STRING,
            'message'   => Schema::TYPE_TEXT,
            'location'  => Schema::TYPE_TEXT,
        ], $tableOptions);

        $this->createTable($i18n->messageTable, [
            'id'            => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'language'      => Schema::TYPE_STRING  . '(16) NOT NULL DEFAULT ""',
            'hash'          => Schema::TYPE_STRING  . '(32) NOT NULL DEFAULT ""',
            'translation'   => Schema::TYPE_TEXT,
        ], $tableOptions);
        $this->addPrimaryKey('id', $i18n->messageTable, ['id', 'language']);
        $this->addForeignKey('fk_source_message_message', $i18n->messageTable, 'id', $i18n->sourceMessageTable, 'id', 'cascade');
    }

    /**
     * @return boolean
     * @throws InvalidConfigException
     */
    public function safeDown()
    {
        if ( !YII_ENV_PROD ) {
            $i18n = Yii::$app->getI18n();
            if (!isset($i18n->sourceMessageTable) || !isset($i18n->messageTable)) {
                throw new InvalidConfigException('You should configure i18n component');
            }

            $this->dropTable($i18n->sourceMessageTable);
            $this->dropTable($i18n->messageTable);

            return true;
        } else {
            echo "WARNING: " . __CLASS__ . " cannot be reverted." . PHP_EOL;

            return false;
        }
    }
}
