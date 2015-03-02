<?php
namespace sanex\filter\components;

use sanex\filter\components\FilterData;
use yii\base\Exception;

class FilterDataPostRequest extends FilterData
{
	protected function setWhereArray()
	{
        foreach ($this->filter as $name => $properties) {            
            if(array_search($name, array_keys($this->model->attributes))) {
                $this->where[$name] = explode(',', $properties['properties']); 
            } else {
                $this->getParams[$name] = explode(',', $properties['properties']);
            }       
        } 
        return $this;
    }
}	