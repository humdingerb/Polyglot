<?php

namespace Tests\Unit;

use Polyglot\FileTypes\LineSeparatedFile;
use PHPUnit\Framework\TestCase;

class LineSeparatedFileTest extends TestCase
{
    const RESOURCES_DIR = __DIR__ . '/resources/';

    private $instance;

    public function setUp() : void
    {
        $this->instance = new LineSeparatedFile(['separator' => '%']);
    }

    public function testProcessOneTextTest()
    {
        $contents = file_get_contents(self::RESOURCES_DIR . 'one.txt');
        $catkeys = $this->instance->process($contents);
        $expected = [
            0 => [
                'text' => "test\nonly one text\nhere",
                'context' => '',
                'comment' => 1,
                'translation' => "test\nonly one text\nhere"
            ]
        ];
        $this->assertIsArray($catkeys);
        $this->assertEquals($expected, $catkeys);
    }

    public function testProcessManyTextsTest()
    {
        $contents = file_get_contents(self::RESOURCES_DIR . 'many.txt');
        $catkeys = $this->instance->process($contents);
        $expected = [
            0 => [
                'text' => "test\nonly one text\nhere",
                'context' => '',
                'comment' => 1,
                'translation' => "test\nonly one text\nhere"
            ],
            1 => [
                'text' => "more here",
                'context' => '',
                'comment' => 2,
                'translation' => "more here"
            ],
            2 => [
                'text' => "and even more\nlines",
                'context' => '',
                'comment' => 3,
                'translation' => "and even more\nlines"
            ],
        ];
        $this->assertIsArray($catkeys);
        $this->assertEquals($expected, $catkeys);
    }

    public function testProcessWithEmptyTextTest()
    {
        $contents = file_get_contents(self::RESOURCES_DIR . 'empty_text.txt');
        $catkeys = $this->instance->process($contents);
        $expected = [
            0 => [
                'text' => "w",
                'context' => '',
                'comment' => 1,
                'translation' => "w"
            ],
            1 => [
                'text' => "",
                'context' => '',
                'comment' => 2,
                'translation' => ""
            ],
            2 => [
                'text' => "a",
                'context' => '',
                'comment' => 3,
                'translation' => "a"
            ],
        ];
        $this->assertIsArray($catkeys);
        $this->assertEquals($expected, $catkeys);
    }

    public function testProcessWithEmptyLastTextTest()
    {
        $contents = file_get_contents(self::RESOURCES_DIR . 'last_empty.txt');
        $catkeys = $this->instance->process($contents);
        $expected = [
            0 => [
                'text' => "w",
                'context' => '',
                'comment' => 1,
                'translation' => "w"
            ],
            1 => [
                'text' => "a",
                'context' => '',
                'comment' => 2,
                'translation' => "a"
            ],
        ];
        $this->assertIsArray($catkeys);
        $this->assertEquals($expected, $catkeys);
    }

    public function testProcessWithEmptySeparatorTest()
    {
        $instance = new LineSeparatedFile(['separator' => '']);
        $contents = file_get_contents(self::RESOURCES_DIR . 'empty_separator.txt');
        $catkeys = $instance->process($contents);
        $expected = [
            0 => [
                'text' => "test",
                'context' => '',
                'comment' => 1,
                'translation' => "test"
            ],
            1 => [
                'text' => "test2",
                'context' => '',
                'comment' => 2,
                'translation' => "test2"
            ],
            2 => [
                'text' => "test3",
                'context' => '',
                'comment' => 3,
                'translation' => "test3"
            ],
        ];
        $this->assertIsArray($catkeys);
        $this->assertEquals($expected, $catkeys);
    }

    public function testProcessWithEmptySeparatorMultilineKeysTest()
    {
        $instance = new LineSeparatedFile(['separator' => '']);
        $contents = file_get_contents(self::RESOURCES_DIR . 'empty_separator_multiline.txt');
        $catkeys = $instance->process($contents);
        $expected = [
            0 => [
                'text' => "test",
                'context' => '',
                'comment' => 1,
                'translation' => "test"
            ],
            1 => [
                'text' => "test2\nhas two lines",
                'context' => '',
                'comment' => 2,
                'translation' => "test2\nhas two lines"
            ],
            2 => [
                'text' => "test3\nhas\nthree",
                'context' => '',
                'comment' => 3,
                'translation' => "test3\nhas\nthree"
            ],
        ];
        $this->assertIsArray($catkeys);
        $this->assertEquals($expected, $catkeys);
    }

    public function testProcessWithEmptySeparatorWhitespaceInSeparatorIsTrimmedTest()
    {
        $instance = new LineSeparatedFile(['separator' => '']);
        $contents = file_get_contents(self::RESOURCES_DIR . 'empty_separator_whitespace.txt');
        $catkeys = $instance->process($contents);
        $expected = [
            0 => [
                'text' => "  test",
                'context' => '',
                'comment' => 1,
                'translation' => "  test"
            ],
            1 => [
                'text' => "test2",
                'context' => '',
                'comment' => 2,
                'translation' => "test2"
            ],
            2 => [
                'text' => "test3",
                'context' => '',
                'comment' => 3,
                'translation' => "test3"
            ],
        ];
        $this->assertIsArray($catkeys);
        $this->assertEquals($expected, $catkeys);
    }

    public function testAssembleTest()
    {
        $keys = [
            0 => [
                'text' => "test\nonly one text\nhere",
                'context' => '',
                'comment' => 1,
                'translation' => "test\nonly one text\nhere"
            ],
            1 => [
                'text' => "more here",
                'context' => '',
                'comment' => 2,
                'translation' => "more here"
            ],
            2 => [
                'text' => "and even more\nlines",
                'context' => '',
                'comment' => 3,
                'translation' => "and even more\nlines"
            ],
        ];
        $expected = file_get_contents(self::RESOURCES_DIR . 'many.txt');
        $this->assertEquals($expected, $this->instance->assemble($keys));
    }

    public function testAssembleWithLastEmptyTest()
    {
        $instance = new LineSeparatedFile(['separator' => '%', 'last_empty' => true]);
        $keys = [
            0 => [
                'text' => "w",
                'context' => '',
                'comment' => 1,
                'translation' => "w"
            ],
            1 => [
                'text' => "a",
                'context' => '',
                'comment' => 2,
                'translation' => "a"
            ],
        ];
        $expected = file_get_contents(self::RESOURCES_DIR . 'last_empty.txt');
        $this->assertEquals($expected, $instance->assemble($keys));
    }

    public function testAssembleWithEmptySeparatorTest()
    {
        $instance = new LineSeparatedFile(['separator' => '']);
        $keys = [
            0 => [
                'text' => "test",
                'context' => '',
                'comment' => 1,
                'translation' => "test"
            ],
            1 => [
                'text' => "test2",
                'context' => '',
                'comment' => 2,
                'translation' => "test2"
            ],
            2 => [
                'text' => "test3",
                'context' => '',
                'comment' => 3,
                'translation' => "test3"
            ],
        ];
        $expected = file_get_contents(self::RESOURCES_DIR . 'empty_separator.txt');
        $this->assertEquals($expected, $instance->assemble($keys));
    }

    public function testProcessAndAssembleSameWithoutLastEmptyTest()
    {
        $contents = file_get_contents(self::RESOURCES_DIR . 'many.txt');
        $keys = $this->instance->process($contents);
        $this->assertEquals($contents, $this->instance->assemble($keys));
    }

    public function testProcessAndAssembleSameWithLastEmptyTest()
    {
        $contents = file_get_contents(self::RESOURCES_DIR . 'last_empty.txt');
        $keys = $this->instance->process($contents);
        $this->assertEquals($contents, $this->instance->assemble($keys));
    }

    public function testProcessAndAssembleSameWithEmptySeparatorTest()
    {
        $instance = new LineSeparatedFile(['separator' => '']);
        $contents = file_get_contents(self::RESOURCES_DIR . 'empty_separator.txt');
        $keys = $instance->process($contents);
        $this->assertEquals($contents, $instance->assemble($keys));
    }
}
