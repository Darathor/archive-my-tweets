<?php
// Global variables.
/** @var $i18n \Darathor\Core\I18n */
$i18n = $this->i18n;
/** @var $config \Darathor\Core\Configuration */
$config = $this->configuration;

// Specific variables.
/** @var $totalItems integer */
/** @var $itemsPerPage integer */
/** @var $totalPages integer */
/** @var $currentPage integer */
/** @var $pageMarker string */
?>

<nav class="amt-pagination">
	<ul class="pagination">
		<li<?php if ($currentPage <= 1) { echo ' class="disabled"'; } ?>>
			<a href="<?php echo $pageMarker . 1; ?>" aria-label="<?php echo $i18n->trans('newest_tweets', ['ucf']); ?>">
				<span aria-hidden="true">«</span>
			</a>
		</li>
		<li<?php if ($currentPage <= 1) { echo ' class="disabled"'; } ?>>
			<a href="<?php echo $pageMarker . max(1, $currentPage - 1); ?>" aria-label="<?php echo $i18n->trans('newer_tweets', ['ucf']); ?>">
				<span aria-hidden="true">‹</span>
			</a>
		</li>
		<?php for ($i = max(1, $currentPage - 3); $i <= min($totalPages, $currentPage + 3); $i ++): ?>
			<li<?php if ($i === $currentPage) { echo ' class="active"'; } ?>>
				<a href="<?php echo $pageMarker . $i; ?>"><?php echo $i; ?></a>
			</li>
		<?php endfor; ?>
		<li<?php if ($currentPage >= $totalPages) { echo ' class="disabled"'; } ?>>
			<a href="<?php echo $pageMarker . min($totalPages, $currentPage + 1); ?>" aria-label="<?php echo $i18n->trans('older_tweets', ['ucf']); ?>">
				<span aria-hidden="true">›</span>
			</a>
		</li>
		<li<?php if ($currentPage >= $totalPages) { echo ' class="disabled"'; } ?>>
			<a href="<?php echo $pageMarker . $totalPages; ?>" aria-label="<?php echo $i18n->trans('oldest_tweets', ['ucf']); ?>">
				<span aria-hidden="true">»</span>
			</a>
		</li>
	</ul>
	<div class="pages">
		<?php echo $i18n->trans('page_of', ['ucf'], ['current_page' => $currentPage, 'total_pages' => $totalPages])  ?>
	</div>
</nav>