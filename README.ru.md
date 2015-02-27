# Simple-Yii2-Checkbox-Filter-Module
Simple Yii2 Checkbox Filter Module

Yii2 filter module v0.03

Видео: http://www.youtube.com/watch?v=Vah2j5WzXIs

#####Принцип работы:
Модуль делает 2 вещи: 
- рисует в View файле список с 'чекбоксами', которыми можно задавать параметры фильтра;
- на основе выбранных параметров фильтра выводит данные указанной модели в указанный дополнительный Ajax View, в основном View контроллера. Следует знать, что этим модулем в Ajax View данные только доставляются, без их визуального отображения. Отобразить данные пользователю необходимо с помощью таблицы или виджета GridView.



#####Установка:
Поместите папку `sanex` в директорию `vendor`, затем добавьте в файл `vendor/yiisoft/extensions.php` следующие строки:
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
..и зарегистрируте модуль в конфигурационном файле Yii2:
```
'modules' => [
    'filter' => [
        'class' => 'sanex\filter\SanexFilter',
    ],
],
```


#####Как использовать?

######Контроллер
В контроллере, к которому пренадлежит основной View, в котором мы хотим вывести чекбоксы и подключить Ajax View, необходимо задать 3 параметра:
```
use sanex\filter\Module;

...

$model = new Catalog; // - модель, данные которой необходимо отфильтровать
$viewFile = '@sanex/catalog/views/catalog/catalog-ajax'; // - алиас или путь к Ajax View, в который необходимо доставлять отфильтрованные данные
$filter = Yii::$app->getModule('filter'); // - объект модуля Фильтр
```

`$viewFile` - Ajax View, вид, в который доставляются отфильтрованные данные. Этот файл с Ajax видом необходимо создать, перед использованием фильтра.

По умолчанию, запрос, сформированный фильтром выглядит вот так: `SELECT COUNT(*) FROM 'catalog' WHERE 'color' IN ('Green', 'Red')`
Чаще всего, возникает необходимость расширить запрос дополнительными условиями. Для этого необходимо вызвать метод setQuery() объекта Фильтр. В качестве параметра этого метода необходимо задать объект `\yii\db\ActiveQuery`, который будет сожержать изначальные условия для выборки, поверх которых будет производиться фильтрация.

```
$query = new \yii\db\ActiveQuery($model);
$query->select(['id', 'name', 'size', 'price', 'country'])->where(['country' => 'Canada'])->orderBy(['price' => SORT_ASC]); 
$filter->setQuery($query);
```

######Вид
В основном View необходимо вызвать метод Фильтра `setFilter()`, в котором задать параметры фильтрации:

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

Можно выбрать между Ajax или не-Ajax работой фильтра, путем установки второго параметра метода setFilter() в значения (bool) true или (bool) false.
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
        ]
    ]
], false);
```

В том месте, где необходимо вывести Ajax View с отфильтрованными данными, необходимо вызвать метод Фильтра renderDataView(), с заданными параметрами:
```
$filter->renderDataView($viewFile, $model, 1, ['testParam' => $testParam]);
```
`renderDataView($viewFile, $model, $setDataProvider = false, $viewParams = [])`

`$viewFile` - Ajax view файл, заданный в контроллере;

`$model` - модель, заданная в контроллере;

`(bool)$setDataProvider` - выбор типа вывода отфильтрованных данных. Если true, то данные выводятся в виде объекта ActiveDataProvider, для последующего вывода в GridView, если false - в виде обычных данных модели, для последующего ручного построения таблицы, для их отображения;

`(array)$viewParams` - параметры, которые будут переданы в AjaxView файл. Принцип работы полностью аналогичен 2-му параметру метода render($view, $params = []).

######В Ajax View, отфильтрованные данные передаются в переменной `$sanexFilterData`.
Далее они могут быть помещены в GridView или на их основе, через цикл, может быть построена таблица.
