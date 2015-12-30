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
 * Aggregate statistics based on contributor.
 */
class Contributors extends Collection implements StatisticsInterface
{
    /**
     * @param Commit $commit
     */
    public function addCommit(Commit $commit)
    {
        $email = $commit->getAuthor()->getEmail();
        $commitDate = $commit->getCommiterDate()->format('Y-m-d');

        if (! isset($this->items[$email])) {
            $this->items[$email] = new Collection();
        }

        $this->items[$email]->items[$commitDate][] = $commit;
        ksort($this->items[$email]->items);
    }

    public function sortCommits()
    {
        uasort($this->items, function ($sortA, $sortB) {
            if (count($sortA) === count($sortB)) {
                return 0;
            }

            return count($sortA) > count($sortB) ? -1 : 1;
        });
    }
}
