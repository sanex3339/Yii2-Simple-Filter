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

        //make css class for checkbox
        foreach ($filter as $key => $property) {
            if (isset($property['class'])) {
                if (is_array($property['class']))
                    $filter[$key]['class'] = implode(' ', $property['class']);
            } else {
                $filter[$key]['class'] = '';
            }
        }

        //set value for js ajax variable
        $ajax = $this->module->ajax == true ? 'true' : 'false';

        return $this->renderPartial('filter-list', ['filter' => $filter, 'ajax' => $ajax]);
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
            'useDataProvider' => $this->module->useDataProvider,
        ]);
        $data = $filterData->getData();

        //set dynamic route for this action
        $this->setRoute($this->module->controllerRoute, 'get');

        $this->module->ajaxViewParams['sanexFilterData'] = $data;

        return $this->renderPartial('filter-data-wrapper', [
            'viewFile' => $this->module->ajaxViewFile, 'viewParams' => $this->module->ajaxViewParams
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
                'filter' => json_decode(Yii::$app->request->post('filter'), true),
                'model' => $parameters['model'],
                'query' => isset($parameters['query']) ? $parameters['query'] : null,
                'useDataProvider' => $parameters['useDataProvider'],
            ]);
            $data = $filterData->getData();

            //set dynamic route for this action
            $this->setRoute($parameters['controllerRoute'], 'post');

            $ajaxViewParams = $parameters['ajaxViewParams'];
            $ajaxViewParams['sanexFilterData'] = $data;
            
            return $this->renderPartial('filter-data-wrapper', [
                'viewFile' => $parameters['ajaxViewFile'], 'viewParams' => $ajaxViewParams
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