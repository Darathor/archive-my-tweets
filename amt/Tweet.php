<?php
namespace Darathor\Amt;

/**
 * @name \Darathor\Amt\Tweet
 */
class Tweet
{
	public $content_id = null; // For a real tweet, its id, for a retweet, the id of the retweeted twee.
	public $id = null;
	public $user_id = null;
	public $user_name = null;
	public $user_screen_name = null;
	public $created_at = null; // YYYY-MM-DD HH:MM:SS
	public $own = null;
	public $reply = null;
	public $favorited = null;
	public $retweeted = null;
	public $retweeted_id = null;
	public $retweeted_user_id = null;
	public $retweeted_user_name = null;
	public $retweeted_user_screen_name = null;
	public $retweeted_created_at = null; // YYYY-MM-DD HH:MM:SS
	public $in_reply_to = null;
	public $in_reply_to_id = null;
	public $in_reply_to_user_id = null;
	public $in_reply_to_user_screen_name = null;
	public $tweet = '';
	public $source = '';
	public $data = null;

	/**
	 * Returns the nicely-formatted date of the tweet.
	 * @param \Darathor\Core\I18n $i18n
	 * @return string
	 */
	public function getFormattedDate(\Darathor\Core\I18n $i18n)
	{
		return $i18n->transDateTime(new \DateTime($this->retweeted ? $this->retweeted_created_at : $this->created_at));
	}

	/**
	 * Returns the nicely-formatted date of the tweet.
	 * @param \Darathor\Core\I18n $i18n
	 * @return string
	 */
	public function getFormattedRetweetDate(\Darathor\Core\I18n $i18n)
	{
		return $this->retweeted ? $i18n->transDateTime(new \DateTime($this->created_at)) : '';
	}

	/**
	 * Returns the tweet text all linked up.
	 * @param \Darathor\Amt\View $view
	 * @return string
	 */
	public function getFormattedTweet(\Darathor\Amt\View $view)
	{
		$status_text = $this->tweet;

		// Replace \n.
		$status_text = str_replace("\n", "<br />\n", $status_text);

		// Replace images.
		$needles = [];
		$replacements = [];
		foreach ($this->getEntities() as $entity)
		{
			$needles[] = $entity->getToken();
			$replacements[] = $entity->isVisual() ? '' : $entity->renderHtmlFragment($view);
		}
		$status_text = str_replace($needles, $replacements, $status_text);

		return $status_text;
	}

	/**
	 * Loads this object from an array.
	 * @param array $t
	 * @return $this
	 */
	public function loadFromArray($t)
	{
		$this->id = $t['id'];
		$this->user_id = $t['user']['id'];
		$this->user_name = $t['user']['name'];
		$this->user_screen_name = $t['user']['screen_name'];
		$this->created_at = date('Y-m-d H:i:s', strtotime($t['created_at']));
		$this->source = $t['source'];
		$this->favorited = (bool)$t['favorited'];
		$this->retweeted = isset($t['retweeted_status']);
		if ($this->retweeted)
		{
			$rt = $t['retweeted_status'];
			$this->tweet = $rt['text'];
			$this->retweeted_id = $rt['id'];
			$this->retweeted_user_id = $rt['user']['id'];
			$this->retweeted_user_name = $rt['user']['name'];
			$this->retweeted_user_screen_name = $rt['user']['screen_name'];
			$this->retweeted_created_at = date('Y-m-d H:i:s', strtotime($rt['created_at']));
			$this->in_reply_to = isset($rt['in_reply_to_status_id']);
			if ($this->in_reply_to)
			{
				$this->in_reply_to = true;
				$this->in_reply_to_id = $rt['in_reply_to_status_id'];
				$this->in_reply_to_user_id = $rt['in_reply_to_user_id'];
				$this->in_reply_to_user_screen_name = $rt['in_reply_to_screen_name'];
			}
		}
		else
		{
			$this->tweet = $t['text'];
			$this->in_reply_to = isset($t['in_reply_to_status_id']);
			if (isset($this->in_reply_to))
			{
				$this->in_reply_to_id = $t['in_reply_to_status_id'];
				$this->in_reply_to_user_id = $t['in_reply_to_user_id'];
				$this->in_reply_to_user_screen_name = $t['in_reply_to_screen_name'];
			}
		}
		$this->finalizeLoad();
		$this->data = $t;
		return $this;
	}

	/**
	 * Loads this object from another object decoded from JSON.
	 * @param Object $t
	 * @return $this
	 */
	public function loadFromJsonObject($t)
	{
		$this->id = $t->id;
		$this->user_id = $t->user->id;
		$this->user_name = $t->user->name;
		$this->user_screen_name = $t->user->screen_name;
		$this->created_at = date('Y-m-d H:i:s', strtotime($t->created_at));
		$this->source = $t->source;
		$this->retweeted = isset($t->retweeted_status);
		if ($this->retweeted)
		{
			$rt = $t->retweeted_status;
			$this->tweet = $rt->text;
			$this->retweeted_id = $rt->id;
			$this->retweeted_user_id = $rt->user->id;
			$this->retweeted_user_name = $rt->user->name;
			$this->retweeted_user_screen_name = $rt->user->screen_name;
			$this->retweeted_created_at = date('Y-m-d H:i:s', strtotime($rt->created_at));
			$this->in_reply_to = isset($rt->in_reply_to_status_id);
			if ($this->in_reply_to)
			{
				$this->in_reply_to_id = $rt->in_reply_to_status_id;
				$this->in_reply_to_user_id = $rt->in_reply_to_user_id;
				$this->in_reply_to_user_screen_name = $rt->in_reply_to_screen_name;
			}
		}
		else
		{
			$this->tweet = $t->text;
			$this->in_reply_to = isset($t->in_reply_to_status_id);
			if ($this->in_reply_to)
			{
				$this->in_reply_to_id = $t->in_reply_to_status_id;
				$this->in_reply_to_user_id = $t->in_reply_to_user_id;
				$this->in_reply_to_user_screen_name = $t->in_reply_to_screen_name;
			}
		}
		$this->finalizeLoad();

		// Not included in JSON.
		$this->favorited = false;
		$this->data = null;
		return $this;
	}

	protected function finalizeLoad()
	{
		$this->content_id = $this->retweeted ? $this->retweeted_id : $this->id;
		if (!$this->retweeted && $this->user_id == TWITTER_ID) // TODO: do not use the constant...
		{
			$this->own = !\Change\Stdlib\StringUtils::beginsWith($this->tweet, '@');
			$this->reply = !$this->own;
		}
		else
		{
			$this->own = false;
			$this->reply = false;
		}
	}

	/**
	 * Loads this object from an object or array (database row).
	 * @param Object|array $row
	 * @return $this
	 */
	public function load($row)
	{
		if (is_object($row))
		{
			foreach ($this as $k => $v)
			{
				if ($k === 'data')
				{
					$this->$k = isset($row->$k) ? json_decode($row->$k, true) : null;
				}
				else
				{
					$this->$k = isset($row->$k) ? $row->$k : null;
				}
			}
		}
		elseif (is_array($row))
		{
			foreach ($this as $k => $v)
			{
				if ($k === 'data')
				{
					$this->$k = isset($row[$k]) ? json_decode($row[$k], true) : null;
				}
				else
				{
					$this->$k = isset($row[$k]) ? $row[$k] : null;
				}
			}
		}
		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getReplyToURL()
	{
		if ($this->in_reply_to_id)
		{
			return 'https://twitter.com/' . $this->in_reply_to_user_screen_name . '/status/' . $this->in_reply_to_id;
		}
		return null;
	}

	/**
	 * @return \Darathor\Amt\Entities\Entity[]
	 */
	public function getEntities()
	{
		$medias = [];
		if (isset($this->data['entities']['hashtags']))
		{
			foreach ($this->data['entities']['hashtags'] as $data)
			{
				$medias[] = new \Darathor\Amt\Entities\Hashtag($data);
			}
		}
		if (isset($this->data['entities']['user_mentions']))
		{
			foreach ($this->data['entities']['user_mentions'] as $data)
			{
				$medias[] = new \Darathor\Amt\Entities\Mention($data);
			}
		}
		if (isset($this->data['extended_entities']['media']))
		{
			foreach ($this->data['extended_entities']['media'] as $data)
			{
				switch ($data['type'])
				{
					case 'photo':
						$medias[] = new \Darathor\Amt\Entities\Image($data);
						break;

					case 'video':
					case 'animated_gif':
						$medias[] = new \Darathor\Amt\Entities\Video($data);
						break;

					default:
						break;
				}
			}
		}
		if (isset($this->data['entities']['urls']))
		{
			foreach ($this->data['entities']['urls'] as $data)
			{
				$medias[] = new \Darathor\Amt\Entities\Link($data);
			}
		}
		return $medias;
	}

	/**
	 * @return \Darathor\Amt\Entities\Entity[]
	 */
	public function getVisuals()
	{
		$medias = [];
		foreach ($this->getEntities() as $entity)
		{
			if ($entity->isVisual())
			{
				$medias[] = $entity;
			}
		}
		return $medias;
	}

	/**
	 * Returns the avatar.
	 * @return \Darathor\Amt\Avatar
	 */
	public function getAvatar()
	{
		$avatar = new \Darathor\Amt\Avatar();
		if (is_array($this->data))
		{
			if ($this->retweeted && isset($this->data['retweeted_status']['user']['profile_image_url_https']))
			{
				$avatar->setUrl($this->data['retweeted_status']['user']['profile_image_url_https']);
			}
			elseif (!$this->retweeted && isset($this->data['user']['profile_image_url_https']))
			{
				$avatar->setUrl($this->data['user']['profile_image_url_https']);
			}
		}
		return $avatar;
	}

	/**
	 * @return \Darathor\Amt\Tweet|null
	 */
	public function getQuotedTweet()
	{
		if (isset($this->data['quoted_status']))
		{
			$quoted = new self();
			$quoted->loadFromArray($this->data['quoted_status']);
			return $quoted;
		}
		elseif (isset($this->data['retweeted_status']['quoted_status']))
		{
			$quoted = new self();
			$quoted->loadFromArray($this->data['retweeted_status']['quoted_status']);
			return $quoted;
		}
		return null;
	}

	/**
	 * @param \Darathor\Amt\View $view
	 * @param string $mode item|single|quote
	 * @return string
	 * @throws \Exception
	 */
	public function renderHtmlFragment(\Darathor\Amt\View $view, $mode = 'item')
	{
		return $view->render('components/tweet.php', ['tweet' => $this, 'mode' => $mode], false);
	}
}