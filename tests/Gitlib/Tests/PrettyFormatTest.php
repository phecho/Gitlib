<?php

/*
 * This file is part of Gitlib.
 *
 * Copyright (C) 2015-2016 The Gitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitlib\Tests;

use Gitlib\PrettyFormat;

class PrettyFormatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataForTestIsParsingPrettyXMLFormat
     */
    public function testIsParsingPrettyXMLFormat($xml, $expected)
    {
        $format = new PrettyFormat();

        $this->assertEquals($expected, $format->parse($xml));
    }

    public function dataForTestIsParsingPrettyXMLFormat()
    {
        return [
            [
                '<item><tag>value</tag><tag2>value2</tag2></item>',
                [['tag' => 'value', 'tag2' => 'value2']],
            ],
            [
                '<item><empty_tag></empty_tag></item>',
                [['empty_tag' => '']],
            ],
            [
                '<item><tag>item 1</tag></item><item><tag>item 2</tag></item>',
                [['tag' => 'item 1'], ['tag' => 'item 2']],
            ],
            [
                '<item><tag><inner_tag>value</inner_tag></tag></item>',
                [['tag' => [['inner_tag' => 'value']]]],
            ],
        ];
    }

    /**
     * @expectedException RuntimeException
     */
    public function testIsNotParsingWithoutData()
    {
        $format = new PrettyFormat();
        $format->parse('');
    }
}
