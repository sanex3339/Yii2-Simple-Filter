<?php
    use sanex\simplefilter\assets\FilterAsset;
    use yii\helpers\Html;
    use yii\web\View;

    FilterAsset::register($this);
    $this->registerJs('var SimpleFilterAjax = '.$ajax, View::POS_HEAD);
?>

<div class="fltr-wrapper">
	<?php foreach ($filter as $property):?>
		<div class='fltr-cat clearfix' data-property='<?=Html::encode($property['property'])?>'>
			<span class="fltr-cat-caption"><?=Html::encode($property['caption'])?></span>
			<?php foreach ($property['values'] as $value):?>
				 <?=Html::a(Html::encode($value), 'javascript:', ['value' => Html::encode($value), 'class' => 'fltr-check '.$property['class'].''])?>
			<?php endforeach;?>
		</div>
	<?php endforeach;?>
</div>