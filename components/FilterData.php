<?php
namespace sanex\simplefilter\components;

use Yii;
use yii\base\UnknownPropertyException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

abstract class FilterData
{
	//init properties
	protected   $filter,
				$model,
				$query,
				$useCache,
				$useDataProvider;

	//class properties
	protected   $data,
				$getParams = [],
				$limit,
				$offset,
				$orderBy,
				$sort,
				$where = [];

	const CACHE_DURATION = 600; //cache duration
	const QUERY_LIMIT = 50; //default limit value for custom or ActiveDataProvider pagination

	public function __construct(Array $properties = [])
	{
		foreach($properties as $key => $value){
			if (property_exists($this, $key)) {
				$this->{$key} = $value;	
			} else {
				throw new UnknownPropertyException("Invalid filter object property", 1);
			}
		}

		if (!$this->model) 
			throw new UnknownPropertyException("Missing model property", 1);

		//set data
		$this->setWhereArray()->setQuery()->setData();
	}

	public function getData()
	{
		return $this->data;
	}

	abstract protected function setWhereArray();

	private function setQuery()
	{
		$query = $this->query ? clone $this->query : $this->model->find();
		if ($query->where)
		   $this->where = array_merge_recursive($query->where, $this->where); 

		$this->limit = $query->limit ? $query->limit : self::QUERY_LIMIT;
		//kittens here
		$this->offset = $this->useDataProvider ? null : 
							($query->offset ? $query->offset :
								(Yii::$app->request->get('page') <= 1 ? 0 : 
									(Yii::$app->request->get('page') - 1) * $this->limit));				
		$this->orderBy = $this->useDataProvider ? null : ($query->orderBy ? $query->orderBy : null); 
		$this->sort = $query->orderBy; //set $this->sort property for dataProvider sorting													

		//build final query
		$this->query = $query->where($this->where)->limit($this->limit)->offset($this->offset)->orderBy($this->orderBy);
		return $this;
	}

	private function setData()
	{
		//set properties array for dataProvider
		$dpProps = ['query' => $this->query, 'pagination' => ['pageSize' => $this->limit]];

		//set dataProvider sorting based on ActiveQuery orderBy() method.
		//sorting based only on first orderBy() parameter
		if ($this->sort)
			$dpProps['sort'] = ['defaultOrder' => [array_keys($this->sort)[0] => SORT_ASC]];

		//set data as ActiveDataProvider or ActiveQuery object
		$this->data = $this->useDataProvider ? new ActiveDataProvider($dpProps) : $this->query;

		//set cached or not cached data
		if ($this->useCache) {
			$data = $this->data;
		    Yii::$app->db->cache(function () use ($data) {
		    	if ($this->useDataProvider) {
		    		return $this->data->prepare(); //set cached dataProvider data
		    	} else {
		    		$this->data = $this->data->all();
		    		return $this->data; //set cached query data
		    	}
		    }, self::CACHE_DURATION);
		} else {
			//set not cached query data. Not cached dataProvider data already in `$this->data`
			if (!$this->useDataProvider)
				$this->data = $this->data->all();
		}
	}
}	