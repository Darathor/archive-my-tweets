<?php
// Global variables.
/** @var $i18n \Darathor\Core\I18n */
$i18n = $this->i18n;
/** @var $config \Darathor\Core\Configuration */
$config = $this->configuration;

// Specific variables.
/** @var $entity \Darathor\Amt\Entities\Image */
?>

<div class="entity entity-image">
	<img src="<?php echo $entity->getLocalUrl(); ?>" alt="<?php echo $entity->getUrl(); ?>" />
	<span class="entity-toolbar">
		<a href="<?php echo $entity->getLocalUrl(); ?>" target="_blank" class="btn btn-default btn-icon"
			title="<?php echo $i18n->trans('view_full_size_image', ['ucf']); ?>">
			<span class="glyphicon glyphicon-fullscreen"></span>
		</a>
		<a href="<?php echo $entity->getExpandedUrl(); ?>" target="_blank" class="btn btn-default btn-icon"
			title="<?php echo $i18n->trans('view_image_on_twitter', ['ucf']); ?>">
			<span class="glyphicon glyphicon-share-alt"></span>
		</a>
	</span>
</div>