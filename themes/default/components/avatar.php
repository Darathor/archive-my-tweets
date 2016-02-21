<?php
// Global variables.
/** @var $i18n \Darathor\Core\I18n */
$i18n = $this->i18n;
/** @var $config \Darathor\Core\Configuration */
$config = $this->configuration;

// Specific variables.
/** @var $avatar \Darathor\Amt\Avatar */
?>

<?php if ($avatar->isValid()): ?>
	<div class="avatar">
		<img src="<?php echo $avatar->getLocalUrl(); ?>" alt="" />
	</div>
<?php endif; ?>