<?php

namespace Classmarkets;

require_once(__DIR__ . '/JavaProperties.php');

class JavaPropertiesTest extends \PHPUnit_Framework_TestCase {
    private $properties;

    protected function setUp() {
        $this->properties = new JavaProperties();
    }

    public function testEmptyString() {
        $props = $this->properties;
        $props->loadString('');
        $this->assertEquals(array(), $props->getAll());
    }

    public function testEmptyFile() {
        $props = $this->properties;
        $props->loadResource('file:///dev/null');
        $this->assertEquals(array(), $props->getAll());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNonExistentFile() {
        $props = $this->properties;
        $props->loadResource(sprintf('file://%s/%s/%s', sys_get_temp_dir(), uniqid(), uniqid()));
        $this->assertEquals(array(), $props->getAll());
    }

    public function testSingleProperty() {
        $props = $this->properties;
        $props->loadString('foo=bar');
        $this->assertEquals(array('foo' => 'bar'), $props->getAll());
    }

    public function testLeadingWhitespace() {
        $props = $this->properties;
        $props->loadString('   foo=bar');
        $this->assertEquals(array('foo' => 'bar'), $props->getAll());
    }

    public function testEmptyLines() {
        $props = $this->properties;
        $props->loadString("\n\n\n");
        $this->assertEquals(array(), $props->getAll());
    }

    public function testCrazyWhitespace() {
        $props = $this->properties;
        $string = <<<_EOS

            foo = bar


  bar=       baz



_EOS;
        $props->loadString($string);
        $this->assertEquals(array('foo' => 'bar', 'bar' => 'baz'), $props->getAll());
    }

    public function testShellComment() {
        $props = $this->properties;
        $props->loadString('#foo=bar');
        $this->assertEquals(array(), $props->getAll());
    }

    public function testBangComment() {
        $props = $this->properties;
        $props->loadString('!foo=bar');
        $this->assertEquals(array(), $props->getAll());
    }

    public function testCommentWithLeadingWhiteSpace() {
        $props = $this->properties;
        $props->loadString('  # foo=bar');
        $this->assertEquals(array(), $props->getAll());
    }

    public function testColonSeparator() {
        $props = $this->properties;
        $props->loadString('foo:bar');
        $this->assertEquals(array('foo' => 'bar'), $props->getAll());
    }

    public function testSpaceSeparator() {
        $props = $this->properties;
        $props->loadString('foo bar');
        $this->assertEquals(array('foo' => 'bar'), $props->getAll());
    }

    public function testSeparatorSurroundedByWhitespace() {
        $props = $this->properties;
        $props->loadString("foo = bar\nbar :\tbaz\n");
        $this->assertEquals(array('foo' => 'bar', 'bar' => 'baz'), $props->getAll());
    }

    public function testLogicalLine() {
        $props = $this->properties;
        $props->loadString("foo = foo bar\\\n   baz\n");
        $this->assertEquals(array('foo' => 'foo barbaz'), $props->getAll());
    }

    public function testEmptyValue() {
        $props = $this->properties;
        $props->loadString("foo =");
        $this->assertEquals(array('foo' => ''), $props->getAll());
    }

    public function testUnicodes() {
        $props = $this->properties;
        $props->loadString("foo = \\u0062\\u0061\\u0072");
        $this->assertEquals(array('foo' => 'bar'), $props->getAll());
    }

    public function testMultipleLoads() {
        $props = $this->properties;

        $props->loadString("foo = bar");
        $this->assertEquals(array('foo' => 'bar'), $props->getAll());

        $props->loadString("bar = baz");
        $this->assertEquals(array('foo' => 'bar', 'bar' => 'baz'), $props->getAll());

        $props->loadString("bar = bar");
        $this->assertEquals(array('foo' => 'bar', 'bar' => 'bar'), $props->getAll());
    }

    /*
    public function testEscapedSeparator() {
        $this->markTestSkipped('not yet implemented');
        $props = $this->properties;
        $props->loadString("foo\\:bar: baz");
        $this->assertEquals(array('foo:bar' => 'baz'), $props->getAll());
    }
     */
}
