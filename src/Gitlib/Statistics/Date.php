<?php

/*
 * This file is part of Gitlib.
 *
 * Copyright (C) 2015-2016 The Gitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitlib\Statistics;

use Gitlib\Model\Commit\Commit;
use Gitlib\Util\Collection;

/**
 * Aggregate statistics based on day
 */
class Date extends Collection implements StatisticsInterface
{
    /**
     * @param Commit $commit
     */
    public function addCommit(Commit $commit)
    {
        $day = $commit->getCommiterDate()->format('Y-m-d');

        $this->items[$day][] = $commit;
    }

    public function sortCommits()
    {
        ksort($this->items);
    }
}
