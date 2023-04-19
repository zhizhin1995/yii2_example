<?php declare(strict_types=1);

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * UserSearch represents the model behind the search form about `webvimark\modules\UserManagement\models\User`.
 */
class PatientSearch extends Patient
{
    /**
     * @param array $params
     * @param string $formName
     * @return ActiveDataProvider
     */
    public function search(array $params, string $formName = 'PatientSearch'): ActiveDataProvider
    {
        $query = self::find();

        $query->with(["status", "polyclinic", "treatment", "formDisease", "updatedBy"]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->cookies->getValue('_grid_page_size', 100),
            ],
            'sort' => [
                'defaultOrder' => [
                    'updated' => SORT_DESC,
                ],
            ],
        ]);

        if (!($this->load($params, $formName) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'polyclinic_id' => $this->polyclinic_id,
            'status_id' => $this->status_id,
            'form_disease_id' => $this->form_disease_id,
            'treatment_id' => $this->treatment_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'phone', $this->phone]);

        return $dataProvider;
    }
}
