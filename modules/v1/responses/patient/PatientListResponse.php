<?php declare(strict_types=1);

namespace app\modules\v1\responses\patient;

use yii\base\BaseObject;
use yii\data\ActiveDataProvider;

/**
 * @class PatientListResponse
 * @package app\modules\v1\responses\patient
 */
class PatientListResponse extends BaseObject
{
    /**
     * @var int $page
     */
    public $pageNumber;

    /**
     * @var int $page
     */
    public $pageElementsCount;

    /**
     * @var int $pageSize
     */
    public $pageSize;

    /**
     * @var int $total
     */
    public $total;

    /**
     * @var PatientResponse[] $list
     */
    public $list;

    /**
     * PatientListResponse constructor
     *
     * @var ActiveDataProvider $dataProvider
     */
    public function __construct(ActiveDataProvider $dataProvider)
    {
        $this->pageNumber = $dataProvider->pagination->page;
        $this->pageElementsCount = $dataProvider->count;
        $this->pageSize = $dataProvider->pagination->pageSize;
        $this->total = $dataProvider->query->count();

        $list = [];

        foreach ($dataProvider->getModels() as $model) {
            $list[] = new PatientResponse($model);
        }

        $this->list = $list;

        parent::__construct();
    }
}