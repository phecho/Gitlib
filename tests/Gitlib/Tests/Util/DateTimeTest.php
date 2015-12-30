<?php

/*
 * This file is part of Gitlib.
 *
 * Copyright (C) 2015-2016 The Gitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitlib\Tests\Util;

use Gitlib\Util\DateTime;

class DateTimeTest extends \PHPUnit_Framework_TestCase
{
    public function testIsCreatingDateObject()
    {
        $date = new DateTime('2010-01-28T15:00:00+02:00');
        $this->assertEquals($date->format('Y-m-d'), '2010-01-28');
    }

    public function testIsCreatingWithoutTimezone()
    {
        $date = new DateTime('2012-10-10 00:00:00');
        $this->assertEquals($date->format('Y-m-d'), '2012-10-10');
    }

    public function testIsCreatingWithUnixTimestamp()
    {
        $date = new DateTime('@632988000');
        $this->assertEquals($date->format('Y-m-d'), '1990-01-22');
    }

    public function testIsCreatingWithUnixTimestampAndTimezone()
    {
        $date = new DateTime('@632988000', new \DateTimeZone('UTC'));
        $this->assertEquals($date->format('Y-m-d'), '1990-01-22');
    }
}
