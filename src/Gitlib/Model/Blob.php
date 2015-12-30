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

use Gitlib\Repository;

class Blob
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var string
     */
    protected $mimetype;

    /**
     * @var string
     */
    protected $content;

    public function __construct($hash, Repository $repository)
    {
        $this->hash = $hash;
        $this->repository = $repository;
    }

    public function getContent()
    {
        if (null === $this->content) {
            $this->content = $this->repository->getClient()->run($this->repository, 'show '.$this->hash);
        }

        return $this->content;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Determine the mimetype of the blob.
     *
     * @return string A mimetype
     */
    public function getMimeType()
    {
        if (null === $this->mimetype) {
            $finfo = new \finfo(FILEINFO_MIME);
            $this->mimetype = $finfo->buffer($this->getContent());
        }

        return $this->mimetype;
    }

    /**
     * Determines if file is binary.
     *
     * @return bool
     */
    public function isBinary()
    {
        return !$this->isText();
    }

    /**
     * Determines if file is text.
     *
     * @return bool
     */
    public function isText()
    {
        return (bool) preg_match('#^text/|^application/xml#', $this->getMimeType());
    }

    /**
     * Determines if it is blob.
     *
     * @return bool
     */
    public function isBlob()
    {
        return true;
    }
}
