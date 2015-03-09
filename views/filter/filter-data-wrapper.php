<?php
	use yii\web\View;
	$this->registerJs('var SimpleFilterAjaxUrl = "'.\Yii::$app->getUrlManager()->createAbsoluteUrl('/simple-filter-ajax').'";', View::POS_HEAD);
?>

<div class="fltr-data-wrapper">
	<?=$this->render($viewFile, $viewParams)?>
</div>