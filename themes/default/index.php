<?php
// Global variables.
/** @var $i18n \Darathor\Amt\View */
$view = $this;
/** @var $i18n \Darathor\Core\I18n */
$i18n = $this->i18n;
/** @var $config \Darathor\Core\Configuration */
$config = $this->configuration;

// Specific variables.
/** @var $title string|null */
/** @var $subTitle string|null */
/** @var $tweets \Darathor\Amt\Tweet|false */
/** @var $twitterMonths array|false */
/** @var $archive_year string */
/** @var $archive_month string */
/** @var $maxTweets integer */
/** @var $totalTweets integer */
/** @var $filters array */
?>

<!-- index -->
<div class="col-sm-8">
	<div id="tweets" class="rounded">
		<?php
			if ($title):
		?>
		<div class="page-header">
			<h1><?php echo $title . ($subTitle ? ' <small>' . $subTitle . '</small>' : ''); ?></h1>
			<form method="GET" action="" class="filters">
				<label class="checkbox-inline">
					<input type="checkbox"<?php echo $filters['own'] ? ' checked="checked"' : ''; ?> name="f[own]" value="1">
					<?php echo $i18n->trans('filter_own'); ?>
				</label>
				<label class="checkbox-inline">
					<input type="checkbox"<?php echo $filters['replies'] ? ' checked="checked"' : ''; ?> name="f[replies]" value="1">
					<?php echo $i18n->trans('filter_replies'); ?>
				</label>
				<label class="checkbox-inline">
					<input type="checkbox"<?php echo $filters['retweets'] ? ' checked="checked"' : ''; ?> name="f[retweets]" value="1">
					<?php echo $i18n->trans('filter_retweets'); ?>
				</label>
				<label class="checkbox-inline">
					<input type="checkbox"<?php echo $filters['favorites'] ? ' checked="checked"' : ''; ?> name="f[favorites]" value="1">
					<?php echo $i18n->trans('filter_favorites'); ?>
				</label>
				<button type="submit" class="btn btn-default btn-xs">
					<span class="glyphicon glyphicon-ok"></span> <?php echo $i18n->trans('filter_button', ['ucf']); ?>
				</button>
			</form>
		</div>
		<?php
			endif;

			if ($tweets !== false)
			{
				foreach ($tweets as $row)
				{
					$tweet = new \Darathor\Amt\Tweet();
					$tweet->load($row);

					echo $tweet->renderHtmlFragment($view, (isset($single_tweet) && $single_tweet) ? 'single' : 'item');
				}

				if (isset($pagination))
				{
					?>
						<div class="page-footer"><?php echo $pagination; ?></div>
					<?php
				}
			}
			else
			{ ?>
				<p class="no-tweets lead"><?php echo $i18n->trans('no_tweet_found', ['ucf']); ?></p>
		<?php } ?>
	</div><!-- /tweets -->
</div><!-- /col-sm-8 -->

<div class="col-sm-4">
	<div id="sidebar">
		<ul id="archive" class="list-group">
			<li class="list-group-item all-tweets <?php echo (isset($all_tweets) && $all_tweets) ? 'here' : ''; ?>">
				<a href="<?php echo $config->get('system', 'baseUrl'); ?>">
					<span class="month"><?php echo $i18n->trans('all_tweets', ['ucf']); ?></span><span class="total"><?php echo $totalTweets; ?></span>
					<span class="bar"></span>
				</a>
			</li>
			<?php
				// months
				if ($twitterMonths)
				{
					$class = '';
					foreach ($twitterMonths as $row)
					{
						$class = (isset($monthly_archive) && $monthly_archive && $archive_year==$row['y'] && $archive_month==$row['m']) ? 'here': '';
						$time = strtotime($row['y'].'-'.$row['m'].'-01');
						$date = date('F Y', $time);
						$url = 'archive/'.date('Y', $time).'/'.date('m', $time).'/';
						$bg_percent = round($row['total'] / $maxTweets * 100);
						echo '<li class="list-group-item '.$class.'"><a href="'.$url.'"><span class="month">'.$date.'</span><span class="total">'.$row['total'].'</span><span class="bar" style="width: '.$bg_percent.'%;"></span></a></li>';
					}
				}
			?>
		</ul><!-- /archive -->
	</div><!-- /sidebar -->
</div><!-- /.col-sm-8 -->
<!-- /index -->