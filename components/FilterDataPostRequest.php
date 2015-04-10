<?php
namespace sanex\simplefilter\components;

use sanex\simplefilter\components\FilterData;

class FilterDataPostRequest extends FilterData
{
    /**
     * set filter `where` statement from parameters in POST request
     *
     * @return $this
     */
    protected function setFilterWhere()
    {
        $values = [];
        foreach ($this->filter as $name => $properties) { 
            $category = explode('[', $name)[0];
            $values[$category][] = array_values($properties)[0];
            foreach ($values as $cat => $val) {
                $valuesString[$cat] = implode(',', $val);
            }
            $filter = $valuesString;  
        }
        if (!empty($filter)) {
            foreach ($filter as $name => $properties) {  
                if(array_search($name, array_keys($this->model->attributes))) {
                    $this->where[$name] = explode(',', $properties); 
                }      
            } 
        }
        
        return $this;
    }
}
