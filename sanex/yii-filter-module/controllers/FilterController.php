<?php

namespace sanex\filter\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\NotFoundHttpException;
use yii\web\Session;

class FilterController extends Controller
{
    private $data, $model, $view;

	public function actions()
    {
        return [
            'error' => [
                'class' => ErrorAction::className()
            ],
        ];
    }

    public function actionSetFilter()
    {
        $module = $this->module;
        $filter = $module->params['filter']['filter'];

        if (!$filter)
            throw new NotFoundHttpException("Invalid or empty filter properties", 1);
        if (!is_array($filter))
            throw new NotFoundHttpException("Filter properties must be as array", 1);

        return $this->renderPartial('filter', [
            'filter' => $filter
        ]);
    }

    public function actionShowDataGet()
    {
        $module = $this->module;
        $modelClass = $module->params['filter']['model'];
        $model = new $modelClass;
        $attributes = $model->attributes();

        $where = array();
        $getParams = array();

        //GET request - create $where array, create $getParams array
        //$where array contain all get parameters with names same as model attributes names
        //$getParams array contain all other get parameters
        if (Yii::$app->request->get('filter') && !Yii::$app->request->getIsAjax())
        {
            $get = Yii::$app->request->get();
            foreach ($get as $category => $property) 
            {
                if (!is_array($property))
                {
                    $property = array($property);
                }

                if(array_search($category, $attributes))
                {
                    $where[$category] = $property;
                } else {
                    $getParams[$category] = $property;
                }       
            }
        } 

        $dataProvider = new ActiveDataProvider([
            'query' => $model->find()->where($where),
            'sort' => false
        ]);

        return $dataProvider;
    }

    public function actionShowDataPost()
    {
        $filter = Yii::$app->getModule('filter')->session['SanexFilter'];
        $modelClass = $filter['model'];
        $view = $filter['view'];

        $model = new $modelClass;
        $attributes = $model->attributes();

        $where = array();
        $getParams = array();

        //POST AJAX request - create $where array, create $getParams array
        //$where array contain all get parameters with names same as model attributes names
        //$getParams array contain all other get parameters
        if (Yii::$app->request->post('filter') && Yii::$app->request->getIsAjax()) {
            $filter = json_decode($_POST['filter'], true);
            foreach ($filter as $name => $properties) 
            {            
                if(array_search($name, $attributes))
                {
                    $where[$name] = explode(',', $properties['properties']); 
                } else {
                    $getParams[$name] = explode(',', $properties['properties']);
                }       
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $model->find()->where($where),
            'sort' => false
        ]);

        //$view = $module->params['filter']['view'];
        return $this->renderPartial($view, [
            'dataProvider' => $dataProvider
        ]);
    }
}
