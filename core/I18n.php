<?php
/**
 * Largely inspired on \Change\I18n\I18nManager from Proximis Omnichannel.
 */
namespace Darathor\Core;

/**
 * @name \Darathor\Core\I18n
 */
class I18n
{
	/**
	 * @var string ex: "fr_FR"
	 */
	protected $defaultLCID = 'fr_FR';

	/**
	 * @var string ex: "fr_FR"
	 */
	protected $LCID;

	/**
	 * @var string
	 */
	protected $translations = [];

	/**
	 * @var string
	 */
	protected $uiDateFormat;

	/**
	 * @var string
	 */
	protected $uiDateTimeFormat;

	/**
	 * @var string
	 */
	protected $uiTimeZone;

	/**
	 * @param string $LCID
	 * @param string $timezone
	 */
	public function __construct($LCID, $timezone)
	{
		$this->setLCID($LCID);
		$this->setTimeZone($timezone);
	}

	/**
	 * Get the LCID.
	 * @api
	 * @return string ex: "fr_FR"
	 */
	public function getLCID()
	{
		return $this->LCID ?: $this->defaultLCID;
	}

	/**
	 * Set the UI LCID.
	 * @api
	 * @throws \InvalidArgumentException if the lang is not supported
	 * @param string $LCID ex: "fr_FR"
	 */
	public function setLCID($LCID)
	{
		$this->LCID = $LCID;
		if (!isset($this->translations[$LCID]))
		{
			$filePath = ROOT_DIR . '/i18n/' . $LCID . '.php';
			if (file_exists($filePath))
			{
				/** @var array $i18n */
				require_once $filePath;
				$this->translations[$LCID] = is_array($i18n) ? $i18n : [];
			}
		}
	}

	/**
	 * For example: trans('c.default_date_format')
	 * @api
	 * @param string $key
	 * @param array $formatters value in array lab, lc, uc, ucf, js, html, attr
	 * @param array $replacements
	 * @return string $cleanKey
	 */
	public function trans($key, $formatters = [], $replacements = [])
	{
		return $this->transForLCID($this->getLCID(), $key, $formatters, $replacements);
	}

	/**
	 * For example: transForLCID('fr_FR', 'c.default_date_format')
	 * @api
	 * @param string $LCID
	 * @param string $key
	 * @param array $formatters value in array lab, lc, uc, ucf, js, attr, raw, text, html
	 * @param array $replacements
	 * @return string
	 */
	public function transForLCID($LCID, $key, $formatters = [], $replacements = [])
	{
		$cleanKey = strtolower($key);
		if (isset($this->translations[$LCID][$cleanKey]))
		{
			$text = $this->translations[$LCID][$cleanKey];
			return $this->formatText($LCID, $text, $formatters, $replacements);
		}
		elseif (isset($this->translations[$this->defaultLCID][$cleanKey]))
		{
			$text = $this->translations[$this->defaultLCID][$cleanKey];
			return $this->formatText($LCID, $text, $formatters, $replacements);
		}
		return '[NOT FOUND] ' . $key;
	}

	/**
	 * For example: formatText('fr_FR', 'My text.')
	 * @api
	 * @param string $LCID
	 * @param string $text
	 * @param array $formatters value in array lab, lc, uc, ucf, js, attr, raw, text, html
	 * @param array $replacements
	 * @return string
	 */
	public function formatText($LCID, $text, $formatters = [], $replacements = [])
	{
		if (count($replacements))
		{
			$search = [];
			$replace = [];
			foreach ($replacements as $key => $value)
			{
				$search[] = '$' . $key . '$';
				$replace[] = $value;
			}
			$text = str_ireplace($search, $replace, $text);
		}

		if (count($formatters))
		{
			$text = $this->dispatchFormatting($text, $formatters, $LCID);
		}
		return $text;
	}

	/**
	 * @param string $text
	 * @param string[] $formatters
	 * @param string $LCID
	 * @internal param string $textFormat
	 * @return string
	 */
	protected function dispatchFormatting($text, $formatters, $LCID)
	{
		foreach ($formatters as $formatter)
		{
			$callable = [$this, 'transform' . ucfirst($formatter)];
			if (is_callable($callable))
			{
				$text = call_user_func($callable, $text, $LCID);
			}
		}
		return $text;
	}

	// Dates.

	/**
	 * @api
	 * @param string $LCID
	 * @return string
	 */
	public function getDateFormat($LCID)
	{
		if ($this->uiDateFormat)
		{
			return $this->uiDateFormat;
		}
		return $this->transForLCID($LCID, 'default_date_format');
	}

	/**
	 * @api
	 * @param string $dateFormat
	 */
	public function setDateFormat($dateFormat)
	{
		$this->uiDateFormat = $dateFormat;
	}

	/**
	 * @api
	 * @param string $LCID
	 * @return string
	 */
	public function getDateTimeFormat($LCID)
	{
		if ($this->uiDateTimeFormat)
		{
			return $this->uiDateTimeFormat;
		}
		return $this->transForLCID($LCID, 'default_datetime_format');
	}

	/**
	 * @api
	 * @param string $dateTimeFormat
	 */
	public function setDateTimeFormat($dateTimeFormat)
	{
		$this->uiDateTimeFormat = $dateTimeFormat;
	}

	/**
	 * @api
	 * @return \DateTimeZone
	 */
	public function getTimeZone()
	{
		if ($this->uiTimeZone)
		{
			return $this->uiTimeZone;
		}
		return new \DateTimeZone('Europe/Paris');
	}

	/**
	 * @api
	 * @param \DateTimeZone|string $timeZone
	 */
	public function setTimeZone($timeZone)
	{
		if (!($timeZone instanceof \DateTimeZone))
		{
			$timeZone = new \DateTimeZone($timeZone);
		}
		$this->uiTimeZone = $timeZone;
	}

	/**
	 * @api
	 * @param \DateTime $gmtDate
	 * @return string
	 */
	public function transDate(\DateTime $gmtDate)
	{
		$LCID = $this->getLCID();
		return $this->formatDate($LCID, $gmtDate, $this->getDateFormat($LCID));
	}

	/**
	 * @api
	 * @param \DateTime $gmtDate
	 * @return string
	 */
	public function transDateTime(\DateTime $gmtDate)
	{
		$LCID = $this->getLCID();
		return $this->formatDate($LCID, $gmtDate, $this->getDateTimeFormat($LCID));
	}

	/**
	 * Format a date.
	 * @api
	 * @param string $LCID
	 * @param \DateTime $gmtDate
	 * @param string $format using this syntax: http://userguide.icu-project.org/formatparse/datetime
	 * @param \DateTimeZone $timeZone
	 * @return string
	 */
	public function formatDate($LCID, \DateTime $gmtDate, $format, $timeZone = null)
	{
		if (!$timeZone)
		{
			$timeZone = $this->getTimeZone();
		}
		$tmpDate = clone $gmtDate; // To not alter $gmtDate.
		$dateFormatter = new \IntlDateFormatter($LCID, null, null, $timeZone->getName(), \IntlDateFormatter::GREGORIAN, $format);
		return $dateFormatter->format($this->toLocalDateTime($tmpDate));
	}

	/**
	 * @api
	 * @param string $date
	 * @return \DateTime
	 */
	public function getGMTDateTime($date)
	{
		return new \DateTime($date, new \DateTimeZone('UTC'));
	}

	/**
	 * @api
	 * @param \DateTime $localDate
	 * @return \DateTime
	 */
	public function toGMTDateTime($localDate)
	{
		return $localDate->setTimezone(new \DateTimeZone('UTC'));
	}

	/**
	 * @api
	 * @param string $date
	 * @return \DateTime
	 */
	public function getLocalDateTime($date)
	{
		return new \DateTime($date, $this->getTimeZone());
	}

	/**
	 * @api
	 * @param \DateTime $localDate
	 * @return \DateTime
	 */
	public function toLocalDateTime($localDate)
	{
		return $localDate->setTimezone($this->getTimeZone());
	}

	// Transformers.

	/**
	 * @param string $text
	 * @param string $LCID
	 * @return string
	 */
	public function transformLab($text, $LCID)
	{
		return $text . (substr($LCID, 0, 2) === 'fr' ? ' :' : ':');
	}

	/**
	 * @param string $text
	 * @param string $LCID
	 * @return string
	 */
	public function transformUc($text, $LCID)
	{
		return \Change\Stdlib\StringUtils::toUpper($text);
	}

	/**
	 * @param string $text
	 * @param string $LCID
	 * @return string
	 */
	public function transformUcf($text, $LCID)
	{
		return \Change\Stdlib\StringUtils::ucfirst($text);
	}

	/**
	 * @param string $text
	 * @param string $LCID
	 * @return string
	 */
	public function transformUcw($text, $LCID)
	{
		return mb_convert_case($text, MB_CASE_TITLE, 'UTF-8');
	}

	/**
	 * @param string $text
	 * @param string $LCID
	 * @return string
	 */
	public function transformLc($text, $LCID)
	{
		return \Change\Stdlib\StringUtils::toLower($text);
	}

	/**
	 * @param string $text
	 * @param string $LCID
	 * @return string
	 */
	public function transformJs($text, $LCID)
	{
		return str_replace(["\\", "\t", "\n", "\"", "'"], ["\\\\", "\\t", "\\n", "\\\"", "\\'"], $text);
	}

	/**
	 * @param string $text
	 * @param string $LCID
	 * @return string
	 */
	public function transformHtml($text, $LCID)
	{
		return nl2br(htmlspecialchars($text, ENT_COMPAT, 'UTF-8'));
	}

	/**
	 * @param string $text
	 * @param string $LCID
	 * @return string
	 */
	public function transformText($text, $LCID)
	{
		if ($text === null)
		{
			return '';
		}
		$text = str_replace(['</div>', '</p>', '</li>', '</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>', '</tr>'],
			PHP_EOL, $text);
		$text = str_replace(['</td>', '</th>'], "\t", $text);
		$text = preg_replace('/<li[^>]*>/', ' * ', $text);
		$text = preg_replace('/<br[^>]*>/', PHP_EOL, $text);
		$text = preg_replace('/<hr[^>]*>/', '------' . PHP_EOL, $text);
		$text = preg_replace(['/<a[^>]+href="([^"]+)"[^>]*>([^<]+)<\/a>/i', '/<img' . '[^>]+alt="([^"]+)"[^>]*\/>/i'],
			['$2 [$1]', PHP_EOL . "[$1]" . PHP_EOL], $text);
		$text = trim(html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8'));
		return $text;
	}

	/**
	 * @param string $text
	 * @param string $LCID
	 * @return string
	 */
	public function transformAttr($text, $LCID)
	{
		return htmlspecialchars(str_replace(["\t", "\n"], ['&#09;', '&#10;'], $text), ENT_COMPAT, 'UTF-8');
	}

	/**
	 * @param string $text
	 * @param string $LCID
	 * @return string
	 */
	public function transformSpace($text, $LCID)
	{
		return ' ' . $text . ' ';
	}

	/**
	 * @param string $text
	 * @param string $LCID
	 * @return string
	 */
	public function transformEtc($text, $LCID)
	{
		return $text . '...';
	}
}