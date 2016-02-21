<?php
namespace Darathor\Amt;

/**
 * @name \Darathor\Amt\Archiver
 * Interacts with the Twitter API to archive tweets for an account.
 */
class Archiver
{
	const TYPE_FAVORITES = 'favorites';
	const TYPE_TIMELINE = 'timeline';

	protected $username;
	protected $twitter;
	protected $model;

	/**
	 * @param string $username
	 * @param \TijsVerkoyen\Twitter\Twitter $twitter
	 * @param \Darathor\Amt\Model $model
	 */
	public function __construct($username, \TijsVerkoyen\Twitter\Twitter $twitter, \Darathor\Amt\Model $model)
	{
		$this->username = $username;
		$this->twitter = $twitter;
		$this->model = $model;
	}

	/**
	 * Grabs all the latest tweets (up to 3200 because of API limits) and puts them into the database.
	 *
	 * @param string $type 'timeline'|'favorites'
	 * @return string Returns a string with informational output.
	 */
	public function archive($type = \Darathor\Amt\Archiver::TYPE_TIMELINE)
	{
		// This should use a maximum of 16 API calls if the user has 3200+ tweets.

		// api params
		$maxId = null;
		$sinceId = null;
		$userId = null; // not needed if using screen name
		$screenName = $this->username;
		$count = 200;
		$trimUser = null;
		$excludeReplies = false;
		$contributorDetails = true;
		$includeRts = true;
		$includeEntities = true;

		// loop variables
		$str = '';
		$page = 1;
		$gotResults = true;
		$apiCalls = 0;
		$tweetsFound = 0;
		$numAdded = 0;
		$numExceptions = 0;
		$maxExceptions = 5; // Don't get stuck in the loop if twitter is down.
		$maxPage = 2; // 20

		while ($gotResults && $page <= $maxPage)
		{
			$str .= 'max id: ' . (($maxId === null) ? 'null' : $maxId) . PHP_EOL;
			try
			{
				if ($type === 'favorites')
				{
					$tweetResults = $this->twitter->favoritesList($userId, $screenName, $count, $sinceId, $maxId, $includeEntities);
				}
				else
				{
					$tweetResults = $this->twitter->statusesUserTimeline($userId, $screenName, $sinceId, $count, $maxId, $trimUser, $excludeReplies,
						$contributorDetails, $includeRts);
				}
				$apiCalls++;

				$numResults = count($tweetResults);
				$tweetsFound += $numResults;

				if ($numResults == 0)
				{
					$str .= 'NO tweets on page ' . $page . ', exiting.' . PHP_EOL;
					$gotResults = false;
				}
				else
				{
					$newestTweet = $tweetResults[0];
					$oldestTweet = end($tweetResults);

					$str .= $numResults . ' tweets on page ' . $page . ' (oldest: ' . $oldestTweet['id'] . ', newest: ' . $newestTweet['id'] . ')' . PHP_EOL;

					$page++;

					// Add these tweets to the database.
					$tweets = [];
					foreach ($tweetResults as $t)
					{
						$tweet = new Tweet();
						$tweet->loadFromArray($t);
						$tweets[] = $tweet;
					}
					$result = $this->model->addTweets($tweets);

					if ($result === false)
					{
						$str .= 'ERROR INSERTING TWEETS INTO DATABASE: ' . $this->model->getLastErrorMessage() . PHP_EOL;
					}
					else if ($result == 0)
					{
						$str .= 'Zero tweets added.' . PHP_EOL;
					}
					else
					{
						$str .= $result . ' tweets added.' . PHP_EOL;
						$numAdded += $result;
					}

					// set max ID to the ID of the oldest tweet we've received, minus 1
					// be mindful of 32 bit platforms
					$maxId = $this->decrement64BitInteger($oldestTweet['id']);
				}

				// check if we've reached the rate limit
				$rate = $this->twitter->getLastRateLimitStatus();
				if (isset($rate['remaining']) && isset($rate['limit']))
				{
					$str .= $rate['remaining'] . '/' . $rate['limit'] . PHP_EOL;
					if ($rate['remaining'] <= 0)
					{
						$str .= 'API limit reached for this hour. Try again later.' . PHP_EOL;
						$gotResults = false;
					}
				}
				else
				{
					$str .= 'Rate limit headers missing from response. Twitter may be having problems. Try again later.' . PHP_EOL;
					$gotResults = false;
				}
			}
			catch (\Exception $e)
			{
				$str .= 'Exception: ' . $e->getMessage() . PHP_EOL;
				$numExceptions++;

				// break out to avoid infinite looping while twitter is down
				if ($numExceptions >= $maxExceptions)
				{
					$str .= 'Too many connection errors. Twitter may be down. Try again later.' . PHP_EOL;
					$gotResults = false;
				}
			}
		}
		$str .= $apiCalls . ' API calls, ' . $tweetsFound . ' tweets found, ' . $numAdded . ' tweets saved' . PHP_EOL;
		return $str;
	}

	/**
	 * Subtracts 1 from the given integer, with support for 32 bit systems.
	 * Note the return value is a string and not an int.
	 *
	 *
	 * @param string $int A positive, non-zero integer represented as a string.
	 * @return string
	 */
	public function decrement64BitInteger($int)
	{
		if (PHP_INT_SIZE == 8)
		{
			return (string)((int)$int - 1);
		}
		else
		{
			$str = (string)$int;

			// 1 and 0 are special cases with this method.
			if ($str == 1 || $str == 0)
			{
				return (string)($str - 1);
			}

			// Determine if number is negative.
			$negative = $str[0] == '-';

			// Strip sign and leading zeros.
			$str = ltrim($str, '0-+');

			// Loop characters backwards.
			for ($i = strlen($str) - 1; $i >= 0; $i--)
			{
				// Handle negative numbers.
				if ($negative)
				{
					if ($str[$i] < 9)
					{
						$str[$i] = $str[$i] + 1;
						break;
					}
					else
					{
						$str[$i] = 0;
					}
				}
				// Handle positive numbers.
				else
				{
					if ($str[$i])
					{
						$str[$i] = $str[$i] - 1;
						break;
					}
					else
					{
						$str[$i] = 9;
					}
				}
			}
			return ($negative ? '-' : '') . ltrim($str, '0');
		}
	}
}
