<?php declare(strict_types=1);

namespace app\modules\v1\models;

use Yii;
use app\models\Patient;

/**
 * @class PatientAPIModel
 * @package app\modules\v1\models
 */
class PatientAPIModel extends Patient
{
    /**
     * {@inheritDoc}
     */
    public function beforeSave($insert): bool
    {
        $userID = Yii::$app->user->identity->id;

        $date = date('Y-m-d H:i:s');

        if ($insert) {
            $this->created_by = $userID;
            $this->created = $date;
        }

        $this->updated_by = $userID;
        $this->updated = $date;

        return parent::beforeSave($insert);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules[] = [['polyclinic_id', 'treatment_id', 'status_id', 'form_disease_id', 'birthday'], 'required'];

        return $rules;
    }
}