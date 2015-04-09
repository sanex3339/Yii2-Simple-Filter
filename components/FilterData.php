<?php
namespace sanex\simplefilter\components;

use Yii;
use yii\base\UnknownPropertyException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

abstract class FilterData
{
	//init properties
	protected $filter;
	protected $model;
	protected $query;
	protected $useCache;
	protected $useDataProvider;

	//class properties
	protected $data;
	protected $cacheDuration = 600;
	protected $limit;
	protected $offset = null;
	protected $orderBy = null;
	protected $queryLimit = 50;
	protected $sort;
	protected $where = [];

	public function __construct(Array $properties = [])
	{
		foreach($properties as $key => $value){
			if (property_exists($this, $key)) {
				$this->{$key} = $value;
			} else {
				throw new UnknownPropertyException("Invalid filter object property", 1);
			}
		}

		if ($this->useCache) {
			if (is_int($this->useCache)) {
				$this->cacheDuration = $this->useCache;
			} elseif (!is_bool($this->useCache)) {
				throw new UnknownPropertyException("Invalid `useCache` value. `useCache` value must be a boolean or integer", 1);
			}
		}

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

		if ($query->where) {
			$this->where = array_merge_recursive($query->where, $this->where);
		}

		$this->limit = $query->limit ? $query->limit : $this->queryLimit;

		if (!$this->useDataProvider) {
			if ($query->offset) {
				$this->offset = $query->offset;
			} else {
				if (Yii::$app->request->get('page') <= 1) {
					$this->offset = 0;
				} else {
					$this->offset = $this->limit * (Yii::$app->request->get('page') - 1);
				}
			}
		}

		if (!$this->useDataProvider && $query->orderBy) {
			$this->orderBy = $query->orderBy;
		}

		$this->sort = $query->orderBy; //set $this->sort property for dataProvider sorting

		$this->query = $query->where($this->where)->limit($this->limit)->offset($this->offset)->orderBy($this->orderBy);

		return $this;
	}

	private function setData()
	{
		$dpProps = ['query' => $this->query, 'pagination' => ['pageSize' => $this->limit]];

		//set dataProvider sorting based on ActiveQuery orderBy() method.
		//sorting based only on first orderBy() parameter
		if ($this->sort) {
			$dpProps['sort'] = ['defaultOrder' => [array_keys($this->sort)[0] => array_values($this->sort)[0]]];
		}

		$this->data = $this->useDataProvider ? new ActiveDataProvider($dpProps) : $this->query;

		if ($this->useCache) {
			$data = $this->data;
			Yii::$app->db->cache(function () use ($data) {
				if ($this->useDataProvider) {
					return $this->data->prepare(); //set cached dataProvider data
				} else {
					$this->data = $this->data->all();
					return $this->data; //set cached query data
				}
			}, $this->cacheDuration);
		} else {
			//set not cached query data. Not cached dataProvider data already in `$this->data`
			if (!$this->useDataProvider) {
				$this->data = $this->data->all();
			}
		}
	}
}	