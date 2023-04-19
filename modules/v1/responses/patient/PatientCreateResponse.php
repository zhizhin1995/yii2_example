<?php declare(strict_types=1);

namespace app\modules\v1\responses\patient;

/**
 * @class PatientCreateResponse
 * @package app\modules\v1\responses\patient
 */
class PatientCreateResponse
{
    /**
     * @var bool $isSuccess
     */
    public $isSuccess;

    /**
     * @var int $createdUserID
     */
    public $createdUserID;

    /**
     * @var array $errorList
     */
    public $errorList;

    /**
     * PatientCreateResponse constructor
     */
    public function __construct($isSuccess, $createdUserID = null, $errorList = [])
    {
        $this->isSuccess = $isSuccess;
        $this->createdUserID = $createdUserID;
        $this->errorList = $errorList;
    }
}