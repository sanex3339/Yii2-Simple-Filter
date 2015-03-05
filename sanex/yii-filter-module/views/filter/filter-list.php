<?php
    use sanex\filter\assets\FilterAsset;
    use yii\helpers\Html;
    use yii\web\View;

    FilterAsset::register($this);
    $this->registerJs('var SanexFilterAjax = '.$ajax, View::POS_HEAD);
?>

<div class="fltr-wrapper">
	<?php foreach ($filter as $property):?>
		<div class='fltr-cat clearfix' id='<?=Html::encode($property['property'])?>'>
			<span class="fltr-cat-caption"><?=Html::encode($property['caption'])?></span>
			<?php foreach ($property['values'] as $value):?>
				<?php if (!is_array($value)):?>
					<?=Html::a(Html::encode($value), 'javascript:', ['value' => Html::encode($value), 'class' => 'fltr-check '.$property['class'].''])?>
				<?php elseif (is_array($value) && count($value) == 2):?>
					<span class="fltr-range-amount" id="fltr-range-amount-<?=Html::encode($property['property'])?>"></span>
					<div id="fltr-range-<?=Html::encode($property['property'])?>" data-range-from="<?=Html::encode($value[0])?>" data-range-to="<?=Html::encode($value[1])?>" class="fltr-range"></div>
				<?php endif;?>
			<?php endforeach;?>
		</div>
	<?php endforeach;?>
</div>