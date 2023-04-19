<?php declare(strict_types=1);

namespace app\modules\v1\responses\patient;

use app\models\Patient;
use yii\base\BaseObject;

/**
 * @class PatientResponse
 * @package app\modules\v1\responses\patient
 */
class PatientResponse extends BaseObject
{
    /**
     * @var int $id
     */
    public $id;

    /**
     * @var string $fullName
     */
    public $name;

    /**
     * @var string $phone
     */
    public $phone;

    /**
     * @var string $clinicName
     */
    public $clinicName;

    /**
     * @var string $diseaseStatus
     */
    public $diseaseStatus;

    /**
     * @var string $diseaseForm
     */
    public $diseaseForm;

    /**
     * @var string $updatedAt
     */
    public $updatedAt;

    /**
     * @var string $diagnosisDate
     */
    public $diagnosisDate;

    /**
     * @var string $releasedFromClinicAt
     */
    public $recoveryDate;

    /**
     * PatientResponse constructor
     */
    public function __construct(Patient $model)
    {
        $this->id = $model->id;
        $this->clinicName = $model->polyclinic->name;
        $this->recoveryDate = $model->recovery_date;
        $this->diagnosisDate = $model->diagnosis_date;
        $this->diseaseStatus = $model->status->name;
        $this->diseaseForm = $model->formDisease->name;
        $this->phone = $model->phone;
        $this->updatedAt = $model->updated;
        $this->name = $model->name;

        parent::__construct();
    }
}