# Simple-Yii2-Checkbox-Filter-Module
Simple Yii2 Checkbox Filter Module

Yii2 filter module for personal use v0.000001preAlphaBeta

Video: http://www.youtube.com/watch?v=Vah2j5WzXIs

###Right now module may not work for you, because v0.000001preAlphaBeta (but it work for me)

Installation:
Put `sanex` folder to vendor folder, then add to 
`vendor/yiisoft/extensions.php`
following code:
```
'sanex/yii-filter-module' => 
array (
    'name' => 'sanex/yii-filter-module',
    'version' => '9999999-dev',
    'alias' => 
    array (
      '@sanex/filter' => $vendorDir . '/sanex/yii-filter-module',
    ),
    'bootstrap' => 'sanex\\filter\\Bootstrap',
),
```
..and register module in config file:
```
'modules' => [
    'filter' => [
        'class' => 'sanex\filter\SanexFilter',
    ],
],
```
How to use?
In controller, which has view (main view), where you want show data with filter, set 3 parameters:
```
use sanex\filter\Module;

...

$modelClass = 'sanex\catalog\models\Catalog'; // - full class name for model with namespaces
$viewFile = '@sanex/catalog/views/catalog/catalog-ajax'; // - alias (or path) to ajax view, where you want to show data
$filter = Yii::$app->getModule('filter'); // - filter object
```

`$viewFile` - ajax view (not main view!!!). You must create that view file before continue.

In main view, you must call `setFilter()` method contain array with filter parameters:

```
$filter->setFilter([
    [
        'property' => 'color',
        'caption' => 'Цвет',
        'values' => [
            'Red',
            'Green',
            'Blue',
            'Black'
        ]
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
Then, where you want to render ajax view with filtered data, call `renderDataView()`:
```
$filter->renderDataView($viewFile, $modelClass, 1, ['testParam' => $testParam]);
```
`renderDataView($viewFile, $modelClass, $setDataProvider = false, $viewParams = [])`

`$viewFile` - ajax view file;

`$modelClass` - model class;

`(bool)$setDataProvider` - if true - return data as dataProvider, if false or not set - return data as model;

`(array)$viewParams` - parameters, that will be send to ajax view.

In ajax view, you can get filtered data (model or dataProvider) through `$sanexFilterData` variable.
#####Note! Module pass to ajax view only data! You must create in that ajax view `<table></table>` or use GridView widget to show data, same way as with all other Yii2 models!
