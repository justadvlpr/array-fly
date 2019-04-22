<?php

namespace ArrayFly\Test;

use ArrayFly\File;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    public function testEmptyFile()
    {
        $this->expectException('\ArrayFly\Exception\FileLocationException');

        new File('');
    }

    public function testFileDoesNotExist()
    {
        $this->expectException('\ArrayFly\Exception\FileLocationException');

        new File('myarray.php');
    }

    public function testGetValue()
    {
        $file = new File(__DIR__ . '/fixtures/array.php');

        foreach ([1, 2, 3, 4] as $k) {
            $value = $file->getValue('mykey' . $k);
            $this->assertEquals('myvalue' . $k, $value);
        }
    }

    public function testSetValueNoMatchesFoundForKey()
    {
        $file = new File(__DIR__ . '/fixtures/array.php');

        $this->expectException('\ArrayFly\Exception\NoMatchFoundException');

        $file->setValue('thisKeyDoesNotExist', 'randomValue');
    }

    public function testSetValue()
    {
        $file = new File(__DIR__ . '/fixtures/array.php');

        $oldValue = $file->getValue('mykey1');

        $file->setValue('mykey1', 'changed 1', true);

        $newValue = $file->getValue('mykey1');

        $this->assertNotEquals($oldValue, $newValue);

        $file->setValue('mykey1', 'myvalue1', true);
    }

    public function testSetValueBadCombinationOnStrictMode()
    {
        $file = new File(__DIR__ . '/fixtures/bad-array.php');

        $this->expectException('\ArrayFly\Exception\InvalidCombinationException');

        $file->setValue('key1', 'changed 1', false, true);
    }

    public function testSetValueAllCombinationsOnStrictMode()
    {
        $file = new File(__DIR__ . '/fixtures/combinations.php');

        $file
            ->setValue('combination1', 'changed1', true, true)
            ->setValue('combination2', 'changed2', true, true)
            ->setValue('combination3', 'changed3', true, true)
            ->setValue('combination4', 'changed4', true, true);

        $content = file_get_contents(__DIR__ . '/fixtures/combinations.php');

        $c1 = $this->getMatch('combination1', $content, [0 => "'", 1 => "'", 2 => "'", 3 => "'"]);
        $this->assertEquals("'combination1' => 'changed1'", $c1[0]);

        $c2 = $this->getMatch('combination2', $content, [0 => "\"", 1 => "\"", 2 => "\"", 3 => "\""]);
        $this->assertEquals("\"combination2\" => \"changed2\"", $c2[0]);

        $c3 = $this->getMatch('combination3', $content, [0 => "\"", 1 => "\"", 2 => "'", 3 => "'"]);
        $this->assertEquals("\"combination3\" => 'changed3'", $c3[0]);

        $c4 = $this->getMatch('combination4', $content, [0 => "'", 1 => "'", 2 => "\"", 3 => "\""]);
        $this->assertEquals("'combination4' => \"changed4\"", $c4[0]);

        $file
            ->setValue('combination1', 'v1', true, true)
            ->setValue('combination2', 'v2', true, true)
            ->setValue('combination3', 'v3', true, true)
            ->setValue('combination4', 'v4', true, true);
    }

    private function getMatch($key, $content, $combination): array
    {
        $matches = [];

        preg_match(
            "/{$combination[0]}{$key}{$combination[1]} => {$combination[2]}(.*){$combination[3]}/",
            $content,
            $matches
        );

        return $matches;
    }
}