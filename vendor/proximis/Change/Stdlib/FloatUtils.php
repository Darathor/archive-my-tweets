<?php
/**
 * Copyright (C) 2015 Proximis
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Change\Stdlib;

/**
 * @api
 * @name \Change\Stdlib\FloatUtils
 */
class FloatUtils
{
	/**
	 * @param float $v1
	 * @param float $v2
	 * @param float $delta
	 * @return boolean
	 */
	public static function equals($v1, $v2, $delta = 0.000001)
	{
		if ($v1 === $v2)
		{
			return true;
		}
		elseif ($v1 === null || $v2 === null)
		{
			return false;
		}
		return abs((float)$v1 - (float)$v2) <= $delta;
	}
}