<?php declare(strict_types=1);

namespace app\modules\v1\controllers;

use app\models\LoginForm;
use app\models\User;
use app\modules\v1\responses\auth\AuthResponse;
use yii\base\Exception;
use yii\filters\VerbFilter;

/**
 * @class AuthController
 * @package app\modules\v1\controllers
 */
class AuthController extends AbstractAPIController
{
    /**
     * {@inheritDoc}
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors[] = [
            'class' => VerbFilter::class,
            'actions' => [
                'get-token' => ['POST'],
            ]
        ];

        return $behaviors;
    }

    /**
     * POST /v1/auth/get-token
     *
     * @return AuthResponse
     * @throws Exception
     */
    public function actionGetToken(): AuthResponse
    {
        $form = new LoginForm();

        if ($form->load($this->request->post(), '') && $form->validate()) {
            if ($token = User::authAPI($form)) {
                return new AuthResponse(true, 'OK', $token);
            }
        }

        return new AuthResponse(false, 'Invalid credentials', null);
    }
}