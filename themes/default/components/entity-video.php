<?php
// Global variables.
/** @var $i18n \Darathor\Core\I18n */
$i18n = $this->i18n;
/** @var $config \Darathor\Core\Configuration */
$config = $this->configuration;

// Specific variables.
/** @var $entity \Darathor\Amt\Entities\Video */
?>

<div class="entity entity-video">
	<div class="entity entity-video-thumbnail">
		<img src="<?php echo $entity->getThumbnailUrl(); ?>" alt="<?php echo $entity->getUrl(); ?>" />
		<a href="<?php echo $entity->getExpandedUrl(); ?>" target="_blank" class="entity-video-play"
			title="<?php echo $i18n->trans('view_video_on_twitter', ['ucf']); ?>">
			<span class="glyphicon glyphicon-play"></span>
		</a>
	</div>
	<?php
		$text = $entity->getText($i18n);
		if ($text):
	?>
		<a href="<?php echo $entity->getExpandedUrl(); ?>" target="_blank">
			<span class="glyphicon glyphicon-film"></span> <?php echo $text; ?>
		</a>
	<?php endif; ?>
</div>