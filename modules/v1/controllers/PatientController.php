<?php declare(strict_types=1);

namespace app\modules\v1\controllers;

use app\models\PatientSearch;
use app\models\User;
use app\modules\v1\models\PatientAPIModel;
use app\modules\v1\responses\patient\PatientCreateResponse;
use app\modules\v1\responses\patient\PatientListResponse;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;

/**
 * @class PatientController
 * @package app\modules\v1\controllers
 */
class PatientController extends AbstractAPIController
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
                'get-list' => ['GET'],
                'create' => ['PUT'],
            ]
        ];

        $behaviors['bearerAuth'] = [
            'class' => HttpBearerAuth::class,
        ];

        return $behaviors;
    }

    /**
     * GET /v1/patient/get-list
     *
     * @return PatientListResponse
     * @throws ForbiddenHttpException
     */
    public function actionGetList(): PatientListResponse
    {
        if (!User::hasPermission('/patientss/view')) {
            throw new ForbiddenHttpException();
        }

        $searchModel = new PatientSearch();

        $dataProvider = $searchModel->search(
            $this->request->getQueryParams(), ''
        );

        $dataProvider->pagination->pageSize = $this->request->get(
            'pageSize', $dataProvider->pagination->pageSize
        );

        $dataProvider->pagination->page = $this->request->get(
            'page', $dataProvider->pagination->page
        );

        return new PatientListResponse($dataProvider);
    }

    /**
     * PUT /v1/patient/create
     *
     * @return PatientCreateResponse
     * @throws ForbiddenHttpException
     */
    public function actionCreate(): PatientCreateResponse
    {
        if (!User::hasPermission('/patientss/create')) {
            throw new ForbiddenHttpException();
        }

        $model = new PatientAPIModel();

        if (($model->load($this->request->post(), '') && $model->validate()) && $model->save()) {
            return new PatientCreateResponse(true, $model->id);
        } else {
            return new PatientCreateResponse(false, null, $model->getFirstErrors());
        }
    }
}