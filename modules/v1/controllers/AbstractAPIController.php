<?php declare(strict_types=1);

namespace app\modules\v1\controllers;

use app\models\User;
use yii\rest\Controller;
use yii\web\JsonParser;
use yii\web\Request;
use yii\web\Response;
use yii\filters\ContentNegotiator;
use Yii;

/**
 * @class AbstractAPIController
 * @package app\modules\v1\controllers
 */
class AbstractAPIController extends Controller
{
    /**
     * @var Request $request
     */
    public $request;

    /**
     * {@inheritDoc}
     */
    public function init(): void
    {
        Yii::$app->user->enableSession = false;
        Yii::$app->user->enableAutoLogin = false;
        Yii::$app->user->loginUrl = null;
        Yii::$app->user->identityClass = User::class;

        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->request->parsers = [
            'application/json' => JsonParser::class
        ];

        $this->request = Yii::$app->request;

        parent::init();
    }

    /**
     * {@inheritDoc}
     */
    public function behaviors(): array
    {
        $behaviours = parent::behaviors();

        $behaviours[] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];

        return $behaviours;
    }
}