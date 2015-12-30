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

interface StatisticsInterface
{
    public function addCommit(Commit $commit);

    public function sortCommits();
}
