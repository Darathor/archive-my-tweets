<?php
// Global variables.
/** @var $i18n \Darathor\Amt\View */
$view = $this;
/** @var $i18n \Darathor\Core\I18n */
$i18n = $this->i18n;
/** @var $config \Darathor\Core\Configuration */
$config = $this->configuration;

/** @var $tweet \Darathor\Amt\Tweet */

$classes = ['tweet'];
if ($tweet->in_reply_to_id != 0)
{
	$classes[] = 'reply';
}
if ($tweet->favorited)
{
	$classes[] = 'favorited';
}
if ($tweet->retweeted)
{
	$classes[] = 'retweeted';
}
if (!$tweet->retweeted && $tweet->user_screen_name == $config->get('twitter', 'username'))
{
	$classes[] = 'my-tweet';
}
$class = implode(' ', $classes);
?>

<div class="<?php echo $class; ?>">
	<div class="tweet-left">
		<?php echo $tweet->getAvatar()->renderHtmlFragment($view) ?>
	</div><div class="tweet-body">
		<p class="meta">
			<a href="<?php echo ($mode === 'single' ? ('https://twitter.com/' . $tweet->user_screen_name . '/status/') : '').$tweet->id; ?>/" rel="bookmark" class="pull-right"><?php echo $tweet->getFormattedDate($i18n); ?></a>
			<strong class="full-name"><?php echo $tweet->retweeted ? $tweet->retweeted_user_name : $tweet->user_name; ?></strong>
			<a href="https://twitter.com/<?php echo $tweet->retweeted ? $tweet->retweeted_user_screen_name : $tweet->user_screen_name; ?>">@<?php echo $tweet->retweeted ? $tweet->retweeted_user_screen_name : $tweet->user_screen_name ?></a>
			<?php if ($tweet->in_reply_to): ?>
				<a href="<?php echo $tweet->getReplyToURL(); ?>"><?php echo $i18n->trans('in_response_to', [], ['response_to' => $tweet->in_reply_to_user_screen_name]); ?></a>
			<?php endif; ?>
		</p>
		<p class="message">
			<?php echo $tweet->getFormattedTweet($view); ?>
		</p>
		<?php
			$visuals = $tweet->getVisuals();
			$countVisuals = count($visuals);
			if ($countVisuals):
		?>
			<div class="row visuals">
				<?php
					foreach ($visuals as $visual):
				?>
					<div class="col-md-<?php echo floor(12/$countVisuals) ?>">
						<?php echo $visual->renderHtmlFragment($view); ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php
			$quoted = $tweet->getQuotedTweet();
			if ($quoted):
		?>
			<blockquote class="quoted-tweet">
				<?php echo $quoted->renderHtmlFragment($view, 'quote'); ?>
			</blockquote>
		<?php endif; ?>
	</div>
</div>