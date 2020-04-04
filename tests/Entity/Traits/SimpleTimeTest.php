<?php

namespace App\Tests\Entity\Traits;

use App\Entity\Traits\SimpleTime;
use PHPUnit\Framework\TestCase;

/**
 * Class SimpleTimeTest
 * @package App\Tests\Entity\Traits
 */
class SimpleTimeTest extends TestCase
{
    use SimpleTime;

    protected $time;

    public function testSimple()
    {
        $this->assertEmpty($this->getDateTimeStringFrom(''));
        $this->assertEmpty($this->getDateTimeStringFrom('time'));

        $this->time = "2019-10-01";
        $this->assertEquals("2019-10-01", $this->getDateTimeStringFrom('time'));
        $this->time = new \DateTime('now');
        $time = $this->time->format('Y-m-d H:i:s');
        $this->assertEquals($time, $this->getDateTimeStringFrom('time'));
    }
}