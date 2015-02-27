<?php

namespace sanex\filter\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
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

        return $this->renderPartial('filter-list', ['filter' => $filter]);
    }

    /**
     * GET request - create $where array, create $getParams array
     * $where array contain all get parameters with names same as model attributes names
     * $getParams array contain all other get parameters
     */
    public function actionShowDataGet()
    {
        $model = $this->module->model;
        $attributes = $model->attributes();
        $where = $getParams = [];

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

        $query = $this->module->query ? clone $this->module->query : $model->find();
        if ($query->where)
           $where = array_merge_recursive($query->where, $where); 
        $query->where($where);

        $data = $this->module->setDataProvider ? new ActiveDataProvider(['query' => $query, 'pagination' => ['pageSize' => 50]]) : $query->all();

        //set dynamic route for this action
        $this->setRoute($this->module->urlForLinks, 'get');

        $this->module->viewParams['sanexFilterData'] = $data;
        return $this->renderPartial('filter-data-wrapper', [
            'viewFile' => $this->module->viewFile, 'viewParams' => $this->module->viewParams
        ]);
    }

    /**
     * POST AJAX request - create $where array, create $getParams array
     * $where array contain all get parameters with names same as model attributes names
     * $getParams array contain all other get parameters
     */   
    public function actionShowDataPostAjax()
    {
        return $this->module->runAction('filter/show-data-post');
    }

    public function actionShowDataPost()
    {   
        if (Yii::$app->request->post('filter') && Yii::$app->request->getIsAjax()) {
            $parameters = $this->module->session['SanexFilter'];
            $model = $parameters['model'];
            $attributes = $model->attributes();
            $where = $getParams = [];
            
            $filter = json_decode($_POST['filter'], true);
            foreach ($filter as $name => $properties) {            
                if(array_search($name, $attributes)) {
                    $where[$name] = explode(',', $properties['properties']); 
                } else {
                    $getParams[$name] = explode(',', $properties['properties']);
                }       
            }           

            $query = isset($parameters['query']) ? clone $parameters['query'] : $model->find();
            if ($query->where)
                $where = array_merge_recursive($query->where, $where);
            $query->where($where);

            $data = $parameters['setDataProvider'] ? new ActiveDataProvider(['query' => $query, 'pagination' => ['pageSize' => 50]]) : $query->all();

            //set dynamic route for this action
            $this->setRoute($parameters['urlForLinks'], 'post');

            $viewParams = $parameters['viewParams'];
            $viewParams['sanexFilterData'] = $data;
            return $this->renderPartial('filter-data-wrapper', [
                'viewFile' => $parameters['viewFile'], 'viewParams' => $viewParams
            ]);
        } else {
            throw new NotFoundHttpException("Page not found.", 1);
        }
    }

    /**
     * Setting dynamic routes depend on in which controller was created Filter object
     * On this controller route will redirect FilterController get and post actions
     * As result, in all URLs inside Ajax view file $viewFile in href attribute, insteat this url:
     * >> site.com/filter/filter/show-data-post/
     * ..will this url:
     * >> site.com/controller_name_where_we_create_filter_object/
     */
    private function setRoute($url, $type)
    {
        Yii::$app->getUrlManager()->addRules(
            [$url => $type == 'get' ? 'filter/filter/show-data-get' : 'filter/filter/show-data-post']
        );
    }
}