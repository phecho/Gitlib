<?php

/*
 * This file is part of Gitlib.
 *
 * Copyright (C) 2015-2016 The Gitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitlib\Util;

class DateTime extends \DateTime
{
    /**
     * @const The regular expression for an UNIX timestamp
     */
    const UNIX_TIMESTAMP_PATTERN = '/^@\d+$/';

    /**
     * @param string       $time     A date/time string.
     * @param DateTimeZone $timezone A DateTimeZone object representing the desired time zone.
     *
     * @return DateTime A new DateTime instance.
     *
     * @link http://php.net/manual/en/datetime.construct.php
     */
    public function __construct($time = 'now', \DateTimeZone $timezone = null)
    {
        if ($timezone) {
            parent::__construct($time, $timezone);
        } else {
            parent::__construct($time);
        }

        if ($this->isUnixTimestamp($time)) {
            if (! $timezone) {
                $timezone = new \DateTimeZone(date_default_timezone_get());
            }

            $this->setTimezone($timezone);
        }
    }

    /**
     * Checks if an UNIX timestamp is passed.
     *
     * @param string $time A date/time string.
     *
     * @return bool Returns true if the $time parameter is a UNIX timestamp
     */
    protected function isUnixTimestamp($time)
    {
        if (preg_match(self::UNIX_TIMESTAMP_PATTERN, $time)) {
            return true;
        }

        return false;
    }
}
