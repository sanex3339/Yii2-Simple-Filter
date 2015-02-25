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
    public function actionSetFilter()
    {
        $filter = $this->module->filter;
        if (!$filter)
            throw new NotFoundHttpException("Invalid or empty filter properties", 1);
        if (!is_array($filter))
            throw new NotFoundHttpException("Filter properties must be as array", 1);

        return $this->renderPartial('filter-list', [
            'filter' => $filter
        ]);
    }

    /**
     * GET request - create $where array, create $getParams array
     *  $where array contain all get parameters with names same as model attributes names
     *  $getParams array contain all other get parameters
     */
    public function actionShowDataGet()
    {
        $model = new $this->module->modelClass;
        $attributes = $model->attributes();

        $where = [];
        $getParams = [];

        if (Yii::$app->request->get('filter') && !Yii::$app->request->getIsAjax()) {
            $get = Yii::$app->request->get();
            foreach ($get as $category => $property) {
                if (!is_array($property))
                    $property = array($property);

                if(array_search($category, $attributes)) {
                    $where[$category] = $property;
                } else {
                    $getParams[$category] = $property;
                }       
            }
        } 

        if ($this->module->setDataProvider) {
            $data = new ActiveDataProvider([
                'query' => $model->find()->where($where),
                'sort' => false
            ]);
        } else {
            $data = $model->find()->where($where)->all();
        }

        $this->module->viewParams['data'] = $data;
        return $this->renderPartial('filter-data-wrapper', [
            'viewFile' => $this->module->viewFile,
            'viewParams' => $this->module->viewParams
        ]);
    }

    /**
     *POST AJAX request - create $where array, create $getParams array
     *$where array contain all get parameters with names same as model attributes names
     *$getParams array contain all other get parameters
     */   
    public function actionShowDataPost()
    {
        if (Yii::$app->request->post('filter') && Yii::$app->request->getIsAjax()) {
            $parameters = $this->module->session['SanexFilter'];

            $model = new $parameters['modelClass'];
            $attributes = $model->attributes();

            $where = [];
            $getParams = [];
         
            $filter = json_decode($_POST['filter'], true);
            foreach ($filter as $name => $properties) {            
                if(array_search($name, $attributes)) {
                    $where[$name] = explode(',', $properties['properties']); 
                } else {
                    $getParams[$name] = explode(',', $properties['properties']);
                }       
            }

            if ($parameters['setDataProvider']) {
                $data = new ActiveDataProvider([
                    'query' => $model->find()->where($where),
                    'sort' => false
                ]);
            } else {
                $data = $model->find()->where($where)->all();
            }

            $view = $parameters['viewFile'];
            $viewParams = $parameters['viewParams'];
            $viewParams['data'] = $data;

            return $this->renderPartial('filter-data-wrapper', [
                'viewFile' => $view,
                'viewParams' => $viewParams
            ]);
        } else {
            throw new NotFoundHttpException("Page not found.", 1);
        }
    }
}
