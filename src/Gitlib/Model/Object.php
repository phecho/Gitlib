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

class Object extends AbstractModel
{
    protected $hash;

    public function isBlob()
    {
        return false;
    }

    public function isTag()
    {
        return false;
    }

    public function isCommit()
    {
        return false;
    }

    public function isTree()
    {
        return false;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }
}
