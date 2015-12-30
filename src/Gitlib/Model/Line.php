<?php

/*
 * This file is part of Gitlib.
 *
 * Copyright (C) 2015-2016 The Gitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitlib\Model;

class Line extends AbstractModel
{
    protected $line;
    protected $type;

    public function __construct($data)
    {
        if (!empty($data)) {
            if ($data[0] == '@') {
                $this->setType('chunk');
            }

            if ($data[0] == '-') {
                $this->setType('old');
            }

            if ($data[0] == '+') {
                $this->setType('new');
            }
        }

        $this->setLine($data);
    }

    public function getLine()
    {
        return $this->line;
    }

    public function setLine($line)
    {
        $this->line = $line;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}
