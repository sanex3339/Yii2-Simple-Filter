<?php

namespace sanex\filter\controllers;

use sanex\filter\components\FilterDataGetRequest;
use sanex\filter\components\FilterDataPostRequest;
use Yii;
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

        //make css class for checkbox
        foreach ($filter as $key => $property) {
            if (isset($property['class'])) {
                if (is_array($property['class']))
                    $filter[$key]['class'] = implode(' ', $property['class']);
            } else {
                $filter[$key]['class'] = '';
            }
        }

        return $this->renderPartial('filter-list', ['filter' => $filter, 'ajax' => $this->module->ajax]);
    }

    /**
     * GET request - create $where array, create $getParams array
     * $where array contain all get parameters with names same as model attributes names
     * $getParams array contain all other get parameters
     */
    public function actionShowDataGet()
    {
        $filterData = new FilterDataGetRequest([
            'model' => $this->module->model,
            'query' => $this->module->query,
            'setDataProvider' => $this->module->setDataProvider,
        ]);
        $data = $filterData->getData();

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

            $filterData = new FilterDataPostRequest([
                'filter' => json_decode($_POST['filter'], true),
                'model' => $parameters['model'],
                'query' => isset($parameters['query']) ? $parameters['query'] : null,
                'setDataProvider' => $parameters['setDataProvider'],
            ]);
            $data = $filterData->getData();

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