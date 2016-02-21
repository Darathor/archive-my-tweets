<?php
namespace Darathor\Amt;

/**
 * @name \Darathor\Amt\Model
 * The MySQL persistence class.
 */
class Model
{
	/**
	 * @var \PDO
	 */
	protected $db;

	/**
	 * @var string
	 */
	protected $table;

	/**
	 * Constructor
	 *
	 * @param \PDO $db A PDO instance.
	 * @param string $prefix The table prefix.
	 */
	public function __construct($db, $prefix)
	{
		$this->db = $db;
		$this->table = $prefix . 'tweets';
	}

	/**
	 * Returns the table name
	 */
	public function getTableName()
	{
		return $this->table;
	}

	/**
	 * Gets a tweet by ID
	 *
	 * @param int $id
	 * @return array|false Returns the tweet with the given ID or false on failure.
	 */
	public function getTweet($id)
	{
		$stmt = $this->db->prepare('SELECT' . ' * FROM ' . $this->table . ' WHERE id=:id LIMIT 1');
		$stmt->bindValue(':id', $id, \PDO::PARAM_INT);
		$status = $stmt->execute();

		if ($status && $stmt->rowCount())
		{
			return $stmt->fetch();
		}
		else
		{
			return false;
		}
	}

	/**
	 * Gets the tweet that was made before the given tweet ID
	 *
	 * @param int $id The current tweet ID
	 * @return array|false Returns the tweet that was made before the given tweet ID, or false if one was not found.
	 */
	public function getTweetBefore($id)
	{
		$stmt = $this->db->prepare('SELECT' . ' * FROM ' . $this->table . ' WHERE id < :id ORDER BY id desc LIMIT 1');
		$stmt->bindValue(':id', $id, \PDO::PARAM_INT);
		$status = $stmt->execute();

		if ($status && $stmt->rowCount())
		{
			return $stmt->fetch();
		}
		else
		{
			return false;
		}
	}

	/**
	 * Gets the tweet that was made after the given tweet ID
	 *
	 * @param int $id The current tweet ID
	 * @return array|false Returns the tweet that was made after the given tweet ID, or false if one was not found.
	 */
	public function getTweetAfter($id)
	{
		$stmt = $this->db->prepare('SELECT' . ' * FROM ' . $this->table . ' WHERE id > :id ORDER BY id asc LIMIT 1');
		$stmt->bindValue(':id', $id, \PDO::PARAM_INT);
		$status = $stmt->execute();

		if ($status && $stmt->rowCount())
		{
			return $stmt->fetch();
		}
		else
		{
			return false;
		}
	}

	/**
	 * Gets the most recent tweet as a Tweet object
	 *
	 * @return array|false Returns the most recent tweet or false on failure.
	 */
	public function getLatestTweet()
	{
		$stmt = $this->db->prepare('SELECT' . ' * FROM ' . $this->table . ' ORDER BY id desc LIMIT 1');
		$status = $stmt->execute();

		if ($status && $stmt->rowCount())
		{
			return $stmt->fetch();
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param string $k
	 * @param int $offset
	 * @param int $perPage
	 * @param bool $count
	 * @return array|bool
	 */
	public function getSearchResults($k, $offset = 0, $perPage = 50, $count = false)
	{

		if (trim($k) == '')
		{
			return false;
		}

		if ($count)
		{
			$sql = 'SELECT' . ' count(*) as total FROM ' . $this->table . ' WHERE 1 ';
		}
		else
		{
			$sql = 'SELECT' . ' * FROM ' . $this->table . ' WHERE 1 ';
		}

		// split out the quoted items
		// $phrases[0] is an array of full pattern matches (quotes intact)
		// $phrases[1] is an array of strings matched by the first parenthesized subpattern, and so on. (quotes stripped)
		// the .+? means match 1 or more characters, but don't be "greedy", i.e., match the smallest amount
		preg_match_all('/"(.+?)"/', $k, $phrases);
		$words = explode(' ', preg_replace('/".+?"/', '', $k));
		$word_list = array_merge($phrases[1], $words);

		// create the sql statement
		$sql .= 'AND (';
		$wordParams = [];
		$i = 1;
		foreach ($word_list as $word)
		{
			if (strlen($word))
			{
				$key = ':word' . $i;
				$wordParams[$key] = '%' . str_replace(',', '', strtolower($word)) . '%';
				$sql .= '(tweet like ' . $key . ') or ';
				$i++;
			}
		}
		$sql = rtrim($sql, ' or '); // remove that dangling 'or'
		$sql .= ') ORDER BY id desc';

		if (!$count)
		{
			$sql .= ' LIMIT :offset,:perPage';
		}

		// bind each search term
		$stmt = $this->db->prepare($sql);
		foreach ($wordParams as $key => $param)
		{
			$stmt->bindValue($key, $param, \PDO::PARAM_STR);
		}
		if (!$count)
		{
			$stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
			$stmt->bindValue(':perPage', (int)$perPage, \PDO::PARAM_INT);
		}
		$stmt->execute();

		if ($count)
		{
			$row = $stmt->fetch();
			return $row['total'];
		}
		else
		{
			return $stmt->fetchAll();
		}
	}

	/**
	 * @param bool $own
	 * @param bool $replies
	 * @param bool $retweets
	 * @param bool $favorites
	 * @return string
	 */
	protected function getWhereClause($own = true, $replies = true, $retweets = true, $favorites = true)
	{
		$conditions = [];
		if ($own)
		{
			$conditions[] = 'own = 1';
		}
		if ($replies)
		{
			$conditions[] = 'reply = 1';
		}
		if ($retweets)
		{
			$conditions[] = 'retweeted = 1';
		}
		if ($favorites)
		{
			$conditions[] = 'favorited = 1';
		}
		return $conditions ? (' WHERE ' . implode(' OR ', $conditions)) : '';
	}

	/**
	 * @param bool $own
	 * @param bool $replies
	 * @param bool $retweets
	 * @param bool $favorites
	 * @param int $offset
	 * @param int $perPage
	 * @return array
	 */
	public function getTweets($own = true, $replies = true, $retweets = true, $favorites = true, $offset = 0, $perPage = 50)
	{
		$whereClause = $this->getWhereClause($own, $replies, $retweets, $favorites);
		$stmt = $this->db->prepare('SELECT' . ' * FROM ' . $this->table . $whereClause . ' ORDER BY id desc LIMIT :offset,:perPage');
		$stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
		$stmt->bindValue(':perPage', (int)$perPage, \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	/**
	 * @param int $offset
	 * @param int $perPage
	 * @return array
	 */
	public function getFavoriteTweets($offset = 0, $perPage = 50)
	{
		$stmt = $this->db->prepare('SELECT' . ' * FROM ' . $this->table . ' WHERE favorited=1 ORDER BY id desc LIMIT :offset,:perPage');
		$stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
		$stmt->bindValue(':perPage', (int)$perPage, \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	/**
	 * @param int $year
	 * @param int $offset
	 * @param int $perPage
	 * @return array
	 */
	public function getTweetsByYear($year, $offset = 0, $perPage = 50)
	{
		$stmt = $this->db->prepare('SELECT' . ' * FROM ' . $this->table
			. ' WHERE year(created_at) = :year ORDER BY id desc LIMIT :offset, :perPage');
		$stmt->bindValue(':year', (int)$year, \PDO::PARAM_INT);
		$stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
		$stmt->bindValue(':perPage', (int)$perPage, \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	/**
	 * @param int $year
	 * @return int
	 */
	public function getTweetsByYearCount($year)
	{
		$stmt = $this->db->prepare('SELECT' . ' count(*) AS total FROM ' . $this->table
			. ' WHERE year(created_at) = :year ORDER BY id desc');
		$stmt->bindValue(':year', (int)$year, \PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch();
		return (int)$row['total'];
	}

	/**
	 * @param int $year
	 * @param int $month
	 * @param int $offset
	 * @param int $perPage
	 * @return array
	 */
	public function getTweetsByMonth($year, $month, $offset = 0, $perPage = 50)
	{
		$stmt = $this->db->prepare('SELECT' . ' * FROM ' . $this->table
			. ' WHERE year(created_at) = :year AND month(created_at) = :month ORDER BY id desc LIMIT :offset, :perPage');
		$stmt->bindValue(':year', (int)$year, \PDO::PARAM_INT);
		$stmt->bindValue(':month', (int)$month, \PDO::PARAM_INT);
		$stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
		$stmt->bindValue(':perPage', (int)$perPage, \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	/**
	 * @param int $year
	 * @param int $month
	 * @return int
	 */
	public function getTweetsByMonthCount($year, $month)
	{
		$stmt = $this->db->prepare('SELECT' . ' count(*) AS total FROM ' . $this->table
			. ' WHERE year(created_at) = :year AND month(created_at) = :month ORDER BY id desc');
		$stmt->bindValue(':year', (int)$year, \PDO::PARAM_INT);
		$stmt->bindValue(':month', (int)$month, \PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch();
		return (int)$row['total'];
	}

	/**
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 * @param int $offset
	 * @param int $perPage
	 * @return array
	 */
	public function getTweetsByDay($year, $month, $day, $offset = 0, $perPage = 50)
	{
		$stmt = $this->db->prepare('SELECT' . ' * FROM ' . $this->table
			. ' WHERE year(created_at) = :year AND month(created_at) = :month AND dayofmonth(created_at) = :day ORDER BY id desc'
			. ' LIMIT :offset, :perPage');
		$stmt->bindValue(':year', (int)$year, \PDO::PARAM_INT);
		$stmt->bindValue(':month', (int)$month, \PDO::PARAM_INT);
		$stmt->bindValue(':day', (int)$day, \PDO::PARAM_INT);
		$stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
		$stmt->bindValue(':perPage', (int)$perPage, \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	/**
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 * @return int
	 */
	public function getTweetsByDayCount($year, $month, $day)
	{
		$stmt = $this->db->prepare('SELECT' . ' count(*) AS total FROM ' . $this->table
			. ' WHERE year(created_at) = :year AND month(created_at) = :month AND dayofmonth(created_at) = :day ORDER BY id desc');
		$stmt->bindValue(':year', (int)$year, \PDO::PARAM_INT);
		$stmt->bindValue(':month', (int)$month, \PDO::PARAM_INT);
		$stmt->bindValue(':day', (int)$day, \PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch();
		return (int)$row['total'];
	}

	/**
	 * @param string $client
	 * @param int $offset
	 * @param int $perPage
	 * @return array
	 */
	public function getTweetsByClient($client, $offset = 0, $perPage = 50)
	{
		$stmt = $this->db->prepare('SELECT' . ' * FROM ' . $this->table
			. ' WHERE source REGEXP CONCAT("(<a.*>)?", :client, "(</a>)?") ORDER BY id desc LIMIT :offset,:perPage');
		$stmt->bindValue(':client', $client, \PDO::PARAM_STR);
		$stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
		$stmt->bindValue(':perPage', (int)$perPage, \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	/**
	 * @param string $client
	 * @return int
	 */
	public function getTweetsByClientCount($client)
	{
		$stmt = $this->db->prepare('SELECT' . ' count(*) as total FROM ' . $this->table
			. ' WHERE source REGEXP CONCAT("(<a.*>)?", :client, "(</a>)?")');
		$stmt->bindValue(':client', $client, \PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch();
		return (int)$row['total'];
	}

	public function getTwitterMonths()
	{
		$stmt = $this->db->prepare('SELECT' . ' year(created_at) as y, month(created_at) as m, count(*) AS total FROM ' . $this->table
			. ' GROUP BY year(created_at),month(created_at) ORDER BY created_at desc');
		$stmt->execute();
		return $stmt->fetchAll();
	}

	/**
	 * @return int
	 */
	public function getMostTweetsInAMonth()
	{
		$stmt = $this->db->prepare('SELECT' . ' year(created_at) as y, month(created_at) as m, count(*) AS total FROM ' . $this->table
			. ' GROUP BY year(created_at),month(created_at) ORDER BY total desc LIMIT 1');
		$stmt->execute();
		$row = $stmt->fetch();
		return (int)$row['total'];
	}

	public function getTwitterClients()
	{
		$stmt = $this->db->prepare('SELECT' . ' source, count(*) as total, count(source) as c FROM ' . $this->table
			. ' group by source ORDER BY count(source) desc');
		$stmt->execute();
		return $stmt->fetchAll();
	}

	/**
	 * @return int
	 */
	public function getMostPopularClientTotal()
	{
		$stmt =
			$this->db->prepare('SELECT' . ' count(*) AS total FROM ' . $this->table . ' GROUP BY source ORDER BY total desc LIMIT 1');
		$stmt->execute();
		$row = $stmt->fetch();
		return (int)$row['total'];
	}

	/**
	 * @return int
	 */
	public function getTotalTweets()
	{
		$stmt = $this->db->prepare('SELECT' . ' count(*) as total FROM ' . $this->table);
		$stmt->execute();
		$row = $stmt->fetch();
		return (int)$row['total'];
	}

	/**
	 * @return int
	 */
	public function getTotalRetweets()
	{
		$stmt = $this->db->prepare('SELECT' . ' count(*) as total FROM ' . $this->table . ' WHERE retweeted=1');
		$stmt->execute();
		$row = $stmt->fetch();
		return (int)$row['total'];
	}

	/**
	 * @return int
	 */
	public function getTotalFavoriteTweets()
	{
		$stmt = $this->db->prepare('SELECT' . ' count(*) as total FROM ' . $this->table . ' WHERE favorited=1');
		$stmt->execute();
		$row = $stmt->fetch();
		return (int)$row['total'];
	}

	/**
	 * @return int
	 */
	public function getTotalClients()
	{
		$stmt = $this->db->prepare('SELECT' . ' count(distinct source) as total FROM ' . $this->table);
		$stmt->execute();
		$row = $stmt->fetch();
		return (int)$row['total'];
	}

	/**
	 * Adds an array of Tweet objects to the database.
	 *
	 * @param \Darathor\Amt\Tweet[] $tweets An array of Tweet objects.
	 * @return int|boolean Returns the number of tweets added to the database, or returns FALSE if there was a MySQL error.
	 */
	public function addTweets($tweets)
	{
		if (count($tweets))
		{
			// "insert ignore" will ignore rows with an id that already exists in the table
			$sql = 'INSERT' . ' IGNORE INTO `' . $this->table . '` ('
				. '`content_id`,'
				. '`id`,'
				. '`user_id`,'
				. '`user_name`,'
				. '`user_screen_name`,'
				. '`created_at`,'
				. '`tweet`,'
				. '`source`,'
				. '`own`,'
				. '`reply`,'
				. '`favorited`,'
				. '`retweeted`,'
				. '`retweeted_id`,'
				. '`retweeted_user_id`,'
				. '`retweeted_user_name`,'
				. '`retweeted_user_screen_name`,'
				. '`retweeted_created_at`,'
				. '`in_reply_to`,'
				. '`in_reply_to_id`,'
				. '`in_reply_to_user_id`,'
				. '`in_reply_to_user_screen_name`,'
				. '`data`'
				. ') VALUES ';

			$i = 0;
			$params = [];
			$values = [];
			foreach ($tweets as $tweet)
			{
				foreach ($tweet->getEntities() as $entity)
				{
					$entity->download();
				}
				$tweet->getAvatar()->download();

				$quoted = $tweet->getQuotedTweet();
				if ($quoted)
				{
					foreach ($quoted->getEntities() as $entity)
					{
						$entity->download();
					}
					$quoted->getAvatar()->download();
				}

				$params[':content_id' . $i] = $tweet->content_id;
				$params[':id' . $i] = $tweet->id;
				$params[':user_id' . $i] = $tweet->user_id;
				$params[':user_name' . $i] = $tweet->user_name;
				$params[':user_screen_name' . $i] = $tweet->user_screen_name;
				$params[':created_at' . $i] = $tweet->created_at;
				$params[':tweet' . $i] = $tweet->tweet;
				$params[':source' . $i] = $tweet->source;
				$params[':own' . $i] = $tweet->own;
				$params[':reply' . $i] = $tweet->reply;
				$params[':favorited' . $i] = $tweet->favorited;
				$params[':retweeted' . $i] = $tweet->retweeted;
				$params[':retweeted_id' . $i] = $tweet->retweeted_id;
				$params[':retweeted_user_id' . $i] = $tweet->retweeted_user_id;
				$params[':retweeted_user_name' . $i] = $tweet->retweeted_user_name;
				$params[':retweeted_user_screen_name' . $i] = $tweet->retweeted_user_screen_name;
				$params[':retweeted_created_at' . $i] = $tweet->retweeted_created_at;
				$params[':in_reply_to' . $i] = $tweet->in_reply_to;
				$params[':in_reply_to_id' . $i] = $tweet->in_reply_to_id;
				$params[':in_reply_to_user_id' . $i] = $tweet->in_reply_to_user_id;
				$params[':in_reply_to_user_screen_name' . $i] = $tweet->in_reply_to_user_screen_name;
				$params[':data' . $i] = $tweet->data ? json_encode($tweet->data) : null;
				$values[] = '('
					. ':content_id' . $i
					. ',:id' . $i
					. ',:user_id' . $i
					. ',:user_name' . $i
					. ',:user_screen_name' . $i
					. ',:created_at' . $i
					. ',:tweet' . $i
					. ',:source' . $i
					. ',:own' . $i
					. ',:reply' . $i
					. ',:favorited' . $i
					. ',:retweeted' . $i
					. ',:retweeted_id' . $i
					. ',:retweeted_user_id' . $i
					. ',:retweeted_user_name' . $i
					. ',:retweeted_user_screen_name' . $i
					. ',:retweeted_created_at' . $i
					. ',:in_reply_to' . $i
					. ',:in_reply_to_id' . $i
					. ',:in_reply_to_user_id' . $i
					. ',:in_reply_to_user_screen_name' . $i
					. ',:data' . $i
					. ')';
				$i++;
			}

			// join all the value groups together: values(1,2,3),(4,5,6),(6,7,8)
			$sql .= implode(',', $values) . ';';

			// integer params
			$intParamKeys = [
				':content_id',
				':id',
				':user_id',
				':retweeted_id',
				':retweeted_user_id',
				':in_reply_to_id',
				':in_reply_to_user_id'
			];

			$stmt = $this->db->prepare($sql);
			foreach ($params as $key => $value)
			{
				$paramType = \PDO::PARAM_STR;

				// some params are integers that need to be bound correctly
				foreach ($intParamKeys as $intK)
				{
					if (substr($key, 0, strlen($intK)) == $intK)
					{
						$paramType = \PDO::PARAM_INT;
						break;
					}
				}
				$stmt->bindValue($key, $value, $paramType);
			}
			$status = $stmt->execute();
			if (!$status)
			{
				trigger_error(__METHOD__ . ' Error in SQL: ' . implode(', ', $stmt->errorInfo()), E_NOTICE);
			}
			return $stmt->rowCount();
		}

		return 0;
	}

	/**
	 * Returns the last error message from the DB
	 */
	public function getLastErrorMessage()
	{
		$error = $this->db->errorInfo();
		return $error[2];
	}

	/**
	 * Returns true if the database table exists.
	 *
	 * @return boolean Returns true if the database table exists, or false if the table hasn't been created.
	 */
	public function isInstalled()
	{
		$stmt = $this->db->prepare("show tables like '" . $this->table . "'");
		$status = $stmt->execute();
		return ($status && $stmt->rowCount());
	}

	/**
	 * Creates the database table necessary to hold the tweets.
	 *
	 * @return boolean Returns true on success, false on failure.
	 * @throws \Exception if there was an error
	 */
	public function install()
	{
		$stmt = $this->db->prepare('CREATE' . ' TABLE ' . $this->table
			. ' (
				`content_id` bigint(20) unsigned not null unique,
				`id` bigint(20) unsigned not null unique,
				`user_id` bigint(20) unsigned not null,
				`user_name` varchar(50),
				`user_screen_name` varchar(15),
				`created_at` datetime not null,
				`tweet` varchar(140),
				`source` varchar(255),
				`own` tinyint(1),
				`reply` tinyint(1),
				`favorited` tinyint(1),
				`retweeted` tinyint(1),
				`retweeted_id` bigint(20) unsigned,
				`retweeted_user_id` bigint(20) unsigned,
				`retweeted_user_name` varchar(50),
				`retweeted_user_screen_name` varchar(15),
				`retweeted_created_at` datetime,
				`in_reply_to` tinyint(1),
				`in_reply_to_id` bigint(20),
				`in_reply_to_user_id` bigint(20),
				`in_reply_to_user_screen_name` varchar(15),
				`data` longtext CHARACTER SET utf8 COLLATE utf8_bin,
				index(source)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;');

		// TODO: run SQL updates here, each in its own function

		$status = $stmt->execute();
		if (!$status)
		{
			$errorInfo = $stmt->errorInfo();
			Throw new \Exception($errorInfo[2]);
		}
		return $status;
	}

	// TODO: upgrade function for running database migration files
}