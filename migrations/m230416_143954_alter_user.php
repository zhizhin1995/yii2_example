<?php declare(strict_types=1);

use yii\db\Migration;

/**
 * Class m230416_143954_alter_user
 */
class m230416_143954_alter_user extends Migration
{
    /**
     * @const string
     */
    const TABLE_NAME = '{{%user}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->addColumn(self::TABLE_NAME, 'access_token', $this->string()->defaultValue(null));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropColumn(self::TABLE_NAME, 'access_token');
    }
}
