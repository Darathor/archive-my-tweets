<?php
// Global variables.
/** @var $i18n \Darathor\Core\I18n */
$i18n = $this->i18n;
/** @var $config \Darathor\Core\Configuration */
$config = $this->configuration;

// Specific variables.
/** @var $entity \Darathor\Amt\Entities\Mention */
?>

<a href="<?php echo $entity->getExpandedUrl(); ?>" target="_blank" class="entity entity-mention" title="<?php echo $entity->getDisplayName(); ?>">
	<?php echo $entity->getToken(); ?>
</a>