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

    public function testSetValue()
    {
        $file = new File(__DIR__ . '/fixtures/array.php');

        $oldValue = $file->getValue('mykey1');

        $file->setValue('mykey1', 'changed 1', true);

        $newValue = $file->getValue('mykey1');

        $this->assertNotEquals($oldValue, $newValue);

        $file->setValue('mykey1', 'myvalue1', true);
    }

    public function testBadCombinationOnStrictMode()
    {
        $file = new File(__DIR__ . '/fixtures/bad-array.php');

        $this->expectException('\ArrayFly\Exception\InvalidCombinationException');

        $file->setValue('key1', 'changed 1', false, true);
    }
}