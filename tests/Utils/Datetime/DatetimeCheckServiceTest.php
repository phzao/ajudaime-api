<?php

namespace App\Tests\Entity;

use App\Utils\Datetime\DatetimeCheckService;
use App\Utils\Datetime\Interfaces\DatetimeCheckServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @package App\Tests\Entity
 */
class DatetimeCheckServiceTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testDefaultData()
    {
        $checker = new DatetimeCheckService();

        $this->assertInstanceOf(DatetimeCheckServiceInterface::class, $checker);

        $today = new \DateTime();

        $this->assertIsBool($checker->thisDatetimeIsGreaterThanNow($today));

        $this->assertInstanceOf(\DateTime::class, $checker->getMonthInTheFutureFrom($today, 1));
    }

    public function testIfDateIsGreater()
    {
        $checker = new DatetimeCheckService();

        $future = new \DateTime('2050-01-01');

        $this->assertEquals(true, $checker->thisDatetimeIsGreaterThanNow($future));

        $future = new \DateTime('1900-01-01');

        $this->assertEquals(false, $checker->thisDatetimeIsGreaterThanNow($future));
    }

    public function testInvalidDateListShouldFail()
    {
        $checker = new DatetimeCheckService();
        $params  = ["mydate" => "2019-asbsda"];
        $datesList = ["mydate"];

        $this->expectException(\InvalidArgumentException::class);
        $checker->getDatesListConvertedToDatetimeOrFail($datesList, $params);
    }

    public function testExceptionOnGetDatesListConvertedWithWrongParamsShouldFail()
    {
        $checker = new DatetimeCheckService();
        $params = ["mydate" => "2019-asbsda"];
        $datesList =[
            "mydate" => [
                "format" => "Y-m-d",
                "message" => "Due date invalid, should be in format Y-m-d"
            ],
        ];
        $this->expectException(UnprocessableEntityHttpException::class);
        $checker->getDatesListConvertedToDatetimeOrFail($datesList, $params);
    }

    public function testGetDatesListConvertedWithRightParamsShouldSuccess()
    {
        $checker = new DatetimeCheckService();
        $params = ["mydate" => "2019-01-01"];
        $datesList =[
            "mydate" => [
                "format" => "Y-m-d",
                "message" => "Due date invalid, should be in format Y-m-d"
            ],
        ];

        $dates = $checker->getDatesListConvertedToDatetimeOrFail($datesList, $params);

        $this->assertInstanceOf(\DateTime::class, $dates["mydate"]);
    }
}