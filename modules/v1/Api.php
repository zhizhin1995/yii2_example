<?php declare(strict_types=1);

namespace app\modules\v1;

/**
 * modules module definition class
 */
class api extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\v1\controllers';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
    }
}
