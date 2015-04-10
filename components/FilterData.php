<?php
namespace sanex\simplefilter\components;

use Yii;
use yii\base\UnknownPropertyException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

abstract class FilterData
{
	/*
	 *
	 * init properties
	 *
	 */

	/**
	 * @var array
	 */
	protected $filter;

	/**
	 * @var object Model object
	 */
	protected $model;

	/**
	 * @var object yii\db\ActiveQuery object
	 */
	protected $query;

	/**
	 * @var bool
	 */
	protected $useCache;

	/**
	 * @var bool
	 */
	protected $useDataProvider;

	/*
	 *
	 * class properties
	 *
	 */

	/**
	 * @var int Default cache duration in seconds
	 */
	protected $cacheDuration = 600;

	/**
	 * @var yii\db\ActiveQuery object
	 */
	protected $customQuery;

	/**
	 * @var object Data object
	 */
	protected $data;

	/**
	 * @var int Limit
	 */
	protected $limit;

	/**
	 * @var int Offset
	 */
	protected $offset = null;

	/**
	 * @var
	 */
	protected $orderBy = null;

	/**
	 * @var int Default query limit value
	 */
	protected $queryLimit = 50;

	/**
	 * @var
	 */
	protected $sort;

	/**
	 * @var array array of `where` parameters
	 */
	protected $where = [];

	/**
	 * constructor
	 *
	 * @param array $properties
	 * @throws UnknownPropertyException
	 */
	public function __construct(Array $properties = [])
	{
		foreach($properties as $key => $value){
			if (property_exists($this, $key)) {
				$this->{$key} = $value;
			} else {
				throw new UnknownPropertyException("Invalid filter object property", 1);
			}
		}

		$this->setFilterWhere()
			 ->setQuery()
			 ->setData();
	}

	/**
	 * @return object
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @return mixed
	 */
	abstract protected function setFilterWhere();

	/**
	 * $customQuery setter
	 *
	 * @param ActiveQuery $query
	 * @return $this
	 */
	private function setCustomQuery(\yii\db\ActiveQuery $query)
	{
		$this->customQuery = $query;

		return $this;
	}

	/**
	 * set `where` statement, by combining filter `where` with custom `where`
	 *
	 * @return $this
	 */
	private function setCustomWhere()
	{
		if ($this->customQuery->where) {
			$this->where = array_merge_recursive($this->customQuery->where, $this->where);
		}

		return $this;
	}

	/**
	 * set `limit` for custom query
	 *
	 * @return $this
	 */
	private function setCustomLimit()
	{
		$this->limit = $this->customQuery->limit ? $this->customQuery->limit : $this->queryLimit;

		return $this;
	}

	/**
	 * set `offset` for custom query
	 *
	 * @return $this
	 */
	private function setCustomOffset()
	{
		if (!$this->useDataProvider) {
			if ($this->customQuery->offset) {
				$this->offset = $this->customQuery->offset;
			} else {
				if (Yii::$app->request->get('page') <= 1) {
					$this->offset = 0;
				} else {
					$this->offset = $this->limit * (Yii::$app->request->get('page') - 1);
				}
			}
		}

		return $this;
	}

	/**
	 * set `orderBy` for custom query
	 *
	 * @return $this
	 */
	private function setCustomOrderBy()
	{
		if (!$this->useDataProvider && $this->customQuery->orderBy) {
			$this->orderBy = $this->customQuery->orderBy;
		} else {
			$this->sort = $this->customQuery->orderBy; //set $this->sort property for dataProvider sorting
		}

		return $this;
	}

	/**
	 * set final query
	 *
	 * @return $this
	 */
	private function setQuery()
	{
		$query = $this->query ? clone $this->query : $this->model->find();

		$this->setCustomQuery($query)
			 ->setCustomWhere()
			 ->setCustomOffset()
			 ->setCustomLimit()
			 ->setCustomOrderBy();

		$this->query = $query->where($this->where)
			                 ->limit($this->limit)
			                 ->offset($this->offset)
			                 ->orderBy($this->orderBy);

		return $this;
	}

	/**
	 * set data
	 *
	 * @return void
	 */
	private function setData()
	{
		$dpProps = [
			'query' => $this->query,
			'pagination' => ['pageSize' => $this->limit],
		];

		/* set dataProvider sorting based on ActiveQuery orderBy() method
		   sorting based only on first orderBy() parameter */
		if ($this->sort) {
			$dpProps['sort'] = ['defaultOrder' => [array_keys($this->sort)[0] => array_values($this->sort)[0]]];
		}

		$this->data = $this->useDataProvider ? new ActiveDataProvider($dpProps) : $this->query;

		if ($this->useCache) {
			$this->setCachedData($this->data);
		} else {
			/* set not cached query data. Not cached dataProvider data already in `$this->data`!!! */
			if (!$this->useDataProvider) {
				$this->data = $this->data->all();
			}
		}
	}

	/**
	 * set data from cache
	 *
	 * @param $data
	 */
	private function setCachedData($data)
	{
		if (!is_bool($this->useCache)) {
			$this->cacheDuration = (int) $this->useCache;
		}

		$data = $this->data;
		Yii::$app->db->cache(function () use ($data) {
			if ($this->useDataProvider) {
				return $this->data->prepare(); //set cached dataProvider data
			} else {
				$this->data = $this->data->all();
				return $this->data; //set cached query data
			}
		}, $this->cacheDuration);
	}
}
