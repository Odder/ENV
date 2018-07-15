<?php

use ENV\ENV;
use PHPUnit\Framework\TestCase;

class DotenvTest extends TestCase
{
    /**
     * @var string
     */
    private $fixturesFolder;

    public function setUp()
    {
        $this->fixturesFolder = dirname(__DIR__) . '/fixtures/.env/';
    }

    public function testEnvSpacingTrims()
    {
        $env = new ENV($this->fixturesFolder . 'spacing');
        $env->load();

        $this->assertSame('bar', $env->get('VALID_SPACED_AROUND_EQUAL'));
        $this->assertSame('foo bar', $env->get('VALID_SPACED_VARIABLE'));
        $this->assertSame('something', $env->get('INVALID_SPACED_VARIABLE'));

    }

}
