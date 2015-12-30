<?php

/*
 * This file is part of Gitlib.
 *
 * Copyright (C) 2015-2016 The Gitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitlib\Model\Commit;

use Gitlib\Model\Line;

class DiffLine extends Line
{
    protected $numNew;
    protected $numOld;

    public function __construct($data, $numOld, $numNew)
    {
        parent::__construct($data);

        if (! empty($data)) {
            switch ($data[0]) {
                case '@':
                    $this->numOld = '...';
                    $this->numNew = '...';
                    break;
                case '-':
                    $this->numOld = $numOld;
                    $this->numNew = '';
                    break;
                case '+':
                    $this->numOld = '';
                    $this->numNew = $numNew;
                    break;
                default:
                    $this->numOld = $numOld;
                    $this->numNew = $numNew;
            }
        } else {
            $this->numOld = $numOld;
            $this->numNew = $numNew;
        }
    }

    public function getNumOld()
    {
        return $this->numOld;
    }

    public function setNumOld($num)
    {
        $this->numOld = $num;
    }

    public function getNumNew()
    {
        return $this->numNew;
    }

    public function setNumNew($num)
    {
        $this->numNew = $num;
    }
}
