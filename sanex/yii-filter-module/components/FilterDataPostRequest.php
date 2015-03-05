<?php
namespace sanex\filter\components;

use sanex\filter\components\FilterData;
use yii\base\Exception;

class FilterDataPostRequest extends FilterData
{
	protected function setWhereArray()
	{
        foreach ($this->filter as $name => $properties) {    
            if(array_search($name, array_keys($this->model->attributes)) !== false) {
                if (isset($properties['properties'])) {
                    $this->where[$name] = explode(',', $properties['properties']);     
                } else if (isset($properties['range'])) {
                    $range = explode('-', $properties['range']);
                    $this->whereRange[] = ['between', $name, $range[0], $range[1]];
                }
            } else {
                if (isset($properties['properties'])) 
                    $this->getParams[$name] = explode(',', $properties['properties']);
            }       
        } 
        return $this;
    }
}	