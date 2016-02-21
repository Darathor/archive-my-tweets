<?php
// Global variables.
/** @var $i18n \Darathor\Core\I18n */
$i18n = $this->i18n;
/** @var $config \Darathor\Core\Configuration */
$config = $this->configuration;

// Specific variables.
/** @var $entity \Darathor\Amt\Entities\Hashtag */
?>

<a href="<?php echo $entity->getExpandedUrl(); ?>" target="_blank" class="entity entity-hashtag"><?php echo $entity->getToken(); ?></a>