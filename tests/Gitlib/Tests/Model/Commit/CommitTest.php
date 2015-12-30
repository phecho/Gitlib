<?php

/*
 * This file is part of Gitlib.
 *
 * Copyright (C) 2015-2016 The Gitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitlib\Tests\Model\Commit;

use Gitlib\Model\Commit\Commit;
use Gitlib\Util\DateTime;

class CommitTest extends \PHPUnit_Framework_TestCase
{
    public function testImportData()
    {
        $data = [
            'hash'           => '209908f247194b1adc836f2e50f957cb1f11f41c',
            'short_hash'     => '209908f',
            'tree'           => '0a1f6638ccfc6d6b34be8a913144304355d23cc3',
            'parents'        => '6e6951114ccf7b162e2a57b0462b39ca972f476f 1e8fd833f71fd20f8b176c79c705b9f096434126',
            'author'         => 'The Author',
            'author_email'   => 'author@example.com',
            'date'           => '1347372763',
            'commiter'       => 'The Commiter',
            'commiter_email' => 'commiter@example.com',
            'commiter_date'  => '1347372763',
            'message'        => 'Test commit',
            'body'           => 'Test body',
        ];
        $commit = new Commit();
        $commit->importData($data);

        $this->assertEquals('209908f247194b1adc836f2e50f957cb1f11f41c', $commit->getHash());
        $this->assertEquals('209908f', $commit->getShortHash());
        $this->assertEquals('0a1f6638ccfc6d6b34be8a913144304355d23cc3', $commit->getTreeHash());
        $this->assertEquals(['6e6951114ccf7b162e2a57b0462b39ca972f476f', '1e8fd833f71fd20f8b176c79c705b9f096434126'], $commit->getParentsHash());
        $this->assertEquals('The Author', $commit->getAuthor()->getName());
        $this->assertEquals('author@example.com', $commit->getAuthor()->getEmail());
        $this->assertEquals(new DateTime('@1347372763'), $commit->getDate());
        $this->assertEquals('The Commiter', $commit->getCommiter()->getName());
        $this->assertEquals('commiter@example.com', $commit->getCommiter()->getEmail());
        $this->assertEquals(new DateTime('@1347372763'), $commit->getCommiterDate());
        $this->assertEquals('Test commit', $commit->getMessage());
        $this->assertEquals('Test body', $commit->getBody());
    }
}
