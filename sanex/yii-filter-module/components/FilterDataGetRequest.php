<?php
namespace sanex\filter\components;

use sanex\filter\components\FilterData;
use Yii;

class FilterDataGetRequest extends FilterData
{
    protected function setWhereArray()
    {
        if (Yii::$app->request->get('filter')) {
            $get = Yii::$app->request->get();
            foreach ($get as $category => $property) {
                if (!is_array($property))
                    $property = array($property);
                if (array_search($category, array_keys($this->model->attributes))) {
                    $this->where[$category] = $property;
                } else {
                    $this->getParams[$category] = $property;
                }     
            }
        }

        return $this;
    }
}	