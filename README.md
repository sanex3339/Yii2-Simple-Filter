# Yii2-Simple-Filter
Yii2 Simple Filter Module

Yii2 Simple Filter Module v0.7.0
#####Not compatible with older versions, because methods names were changed! 

Video: http://www.youtube.com/watch?v=Vah2j5WzXIs

###Installation:
Install module through [composer](http://getcomposer.org/download/)

Run
```
php composer.phar require --prefer-dist sanex/yii2-simple-filter "*"
```
or add
```
"sanex/yii2-simple-filter": "*"
```
to the `require` section of `composer.json` file.

Last step: add `'enablePrettyUrl' => true` to the `urlManager` in config file.
######Module not working with `'enablePrettyUrl' => false`!


###How to use?

####Controller:
In controller, which has view (main view), where you want show data with filter, you must create object with instance of `SimplyFilter` module and set to him parameters:
```
use sanex\simplyfilter\SimplyFilter;

...

$model = new Catalog;

$ajaxViewFile = '@sanex/catalog/views/catalog/catalog-ajax';

$filter = SimplyFilter::getInstance();
$filter->setParams([
    'ajax' => true,
    'model' => $model,
    'query' => $query,
    'useDataProvider' => true,
]);
```

`$ajaxViewFile` - alias (or path) to ajax view, where you want to show data. You must create that view file before continue.

`setParams()` properties:
`model` - model, which data need to filter;
`ajax` - (optional, if not set - `true`) you can choose between Ajax or non-Ajax filtering by setting this parameter to boolean `true` or `false` values;
`query` - (optional) - \yii\db\ActiveQuery object, see below;
`useDataProvider` - (optional, if not set - `false`) if (bool) true - return data in Ajax View as dataProvider, if (bool) false or not set - return data as model;

As default, result query with filter looks like `SELECT COUNT(*) FROM 'catalog' WHERE 'color' IN ('Green', 'Red')`
If you want create custom query with filter you must set `query` parameter in `setParams()` method with `\yii\db\ActiveQuery` object as method parameter, that contain query parameters.

```
$query = new \yii\db\ActiveQuery($model);
$query->select(['id', 'name', 'size', 'price', 'country'])->where(['country' => 'Canada'])->orderBy(['price' => SORT_ASC]); 
```

Method `limit()` of \yii\db\ActiveQuery object can set parameter `'pagination' => ['pageSize' => $this->limit]`, of ActiveDatapProvider object.

```
$query = new \yii\db\ActiveQuery($model);
$query->limit(25); 
```
If `limit()` method not set, then `limit` for each query will set to default value - 50 rows per page.

If you want to use custom pagination with this filter, you can get `offset` from GET-paremeter `page`. Need to know, what for `page` values `0` and `1`, `offset` value will `0`, for all other values - will calculated by formula `(page - 1) * limit`.

In main view, you must call `setFilter()` method contain array with filter parameters.
######Note! `property` must be same as names of table columns which you want to filter, and `values` must be same as this columns data. 


####Main View

```
$filter->setFilter([
    [
        'property' => 'color',
        'caption' => 'Color',
        'values' => [
            'Red',
            'Green',
            'Blue',
            'Black'
        ],
        'class' => 'horizontal'
    ],
    [
        'property' => 'size',
        'caption' => 'Size',
        'values' => [
            '45x45',
            '50x50',
            '60x60'
        ]
    ]
]);
```

You can set additional class or classes to each filters group by setting `class` property. This filter has two default style for checkbox: `horizontal` and `vertical` class for vertical checkboxes placement. If `class` property not set, used `horizontal` class as default.
You can set `class` value as string:
`'class' => 'horizontal additional class'` 
or as array: 
`'class' => ['vertical', 'additionalClass']`


Then, where you want to render ajax view with filtered data, call `renderAjaxView()` method:
```
$filter->renderAjaxView($ajaxViewFile, ['testParam' => $testParam]);
```
`$ajaxViewFile` - Ajax View file;

`(array)$ajaxViewParams` - (optional) parameters, that will be send to ajax view.


####Ajax View
In ajax view, you can get filtered data (model or dataProvider) through `$sanexFilterData` variable.

######Note! Module pass to ajax view only data! You must create in that ajax view `<table></table>` or use GridView widget to show data, same way as with all other Yii2 models!

######Note! For all urls inside Ajax View, which not used for custom sorting or custom pagination, necessarily add to them `sfCustomUrl` class!!!! To all urls without that class in ajax view appended GET-parameters which are necessary for proper work sorting and pagination (gridView and custom). 
