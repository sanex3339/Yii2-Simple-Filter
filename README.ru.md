# Simple-Yii2-Checkbox-Filter-Module
Simple Yii2 Checkbox Filter Module

Yii2 filter module v0.5.5

Видео: http://www.youtube.com/watch?v=Vah2j5WzXIs

#####Принцип работы:
Модуль делает 2 вещи: 
- рисует в View файле список с 'чекбоксами', которыми можно задавать параметры фильтра;
- на основе выбранных параметров фильтра выводит данные указанной модели в указанный дополнительный Ajax View, в основном View контроллера. Следует знать, что этим модулем в Ajax View данные только доставляются, без их визуального отображения. Отобразить данные пользователю необходимо с помощью таблицы или виджета GridView.



#####Установка (Видео http://www.youtube.com/watch?v=J-S4L85-F6M):
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

Далее, добавьте `'enablePrettyUrl' => true` в настройках `urlManager` в конфигурационном файле.
######Модуль не работает с параметром `'enablePrettyUrl' => false`!


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

Методом `limit()` объекта \yii\db\ActiveQuery можно задать параметр `'pagination' => ['pageSize' => $this->limit]`, (кол-во записей на странице) у объекта ActiveDatapProvider.

```
$query = new \yii\db\ActiveQuery($model);
$query->limit(25); 
$filter->setQuery($query);
```
Если метод `limit()` не указан, то `limit` для всех запросов устанавливается в стандартное значение - 50 записей на странице.

Если с данным фильтром возникла необходимость в создании кастомного виджета постраничной навигации `pagination`, то необходимый для работы виджета сдвиг `offset` можно получить из GET-параметра `page`.Следует учесть, что для значений `page` в `0` и `1`, значение `offset` будет равняться `0`, для остальных значений - по формуле `(page - 1) * limit`.

######Вид
В основном View необходимо вызвать метод Фильтра `setFilter()`, в котором задать параметры фильтрации.
######Значения `property` должны быть такими же, как имена столбцов таблице БД, а `values`, совпадать с содержимым этих столбцов!

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
Вы можете задать дополнительные классы для checkbox'ов, путем задания свойства `class`. Данный фильтр содержит 2 стиля для checkbox'ов фильтра: класс `horizontal` и класс `vertical` для вертикального расположения. Если свойство `class` не установлено, то будет использоваться стиль `horizontal` по умолчанию.
Значение свойства `class` может быть строкой:
`'class' => 'horizontal additional class'` 
или массивом: 
`'class' => ['vertical', 'additionalClass']`


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

#####Для всех ссылок внутри Ajax View, которые не используются для сортировки или постраничной навигации (как у gridView, так и для кастомной), необходимо добавить класс `sfCustomUrl`!!!! К ссылкам без этого класса будут автоматически добавляться GET-параметры, необходимые для работы фильтра, сортировки и постраничной навигации.