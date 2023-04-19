<?php declare(strict_types=1);

namespace app\modules\v1\responses\auth;

use yii\base\BaseObject;

/**
 * @class AuthResponse
 * @package app\modules\v1\responses\auth
 */
class AuthResponse extends BaseObject
{
    /**
     * @var bool $isSuccess
     */
    public $isSuccess;

    /**
     * @var string $message
     */
    public $message;

    /**
     * @var string|null $token
     */
    public $token;

    /**
     * AuthResponse constructor
     */
    public function __construct($isSuccess, $message, $token = null)
    {
        $this->isSuccess = $isSuccess;
        $this->token = $token;
        $this->message = $message;

        parent::__construct();
    }
}