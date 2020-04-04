<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\CreditCard;
use App\Entity\Expense;
use App\Entity\Interfaces\ExpenseInterface;
use App\Entity\Interfaces\ModelInterface;
use App\Entity\Interfaces\ReadUserOutsideInterface;
use App\Entity\Interfaces\SearchDataInterface;
use App\Entity\Interfaces\SimpleTimeInterface;
use App\Entity\User;
use App\Utils\Generators\Bin2HexGenerate;
use PHPUnit\Framework\TestCase;

/**
 * @package App\Tests\Entity
 */
class ExpenseTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testEmptyExpense()
    {
        $expense = new Expense();

        $this->assertInstanceOf(ExpenseInterface::class, $expense);
        $this->assertInstanceOf(ModelInterface::class, $expense);
        $this->assertInstanceOf(SimpleTimeInterface::class, $expense);
        $this->assertInstanceOf(ReadUserOutsideInterface::class, $expense);
        $this->assertInstanceOf(SearchDataInterface::class, $expense);
        $this->assertInstanceOf(\JsonSerializable::class, $expense);

        $this->assertEquals(1, $expense::FIRST_INSTALLMENT);
        $this->assertEquals(1, $expense::MIN_INSTALLMENT);
        $this->assertEquals(125, $expense::LENGTH_STRING_TOKEN);
        $this->assertEquals(0, $expense::VALUE_MUST_BE_GREATER_THAN);
        $this->assertEquals(2, $expense::ROUND_PRECISION);

        $this->assertIsArray($expense->getOriginalData());
        $this->assertEmpty($expense->getId());
        $this->assertEmpty($expense->getPlace());
        $this->assertEmpty($expense->getCategory());

        $this->assertIsArray($expense->getPlaceIdDescription());
        $this->assertEmpty($expense->getPlaceIdDescription());
        $this->assertEmpty($expense->getPlaceId());

        $this->assertIsBool($expense->isToDivideThisExpense());
        $this->assertEquals(false, $expense->isToDivideThisExpense());

        $this->assertIsArray($expense->getParamsToListAll([]));
        $this->assertEmpty($expense->getParamsToListAll([]));

        $this->assertIsInt($expense->getTotalTimesToDivideThisExpense());
        $this->assertEquals(1,$expense->getTotalTimesToDivideThisExpense());

        $this->assertEmpty($expense->getRegisteredBy());
        $this->assertIsBool($expense->thisExpenseIsPartOfGroup());

        $this->assertEquals([
                                "due_date" => [
                                    "format" => "Y-m-d",
                                    "message" => "Due date invalid, should be in format Y-m-d"
                                ],
                                "paid_at" => [
                                    "format" => "Y-m-d H:i:s",
                                    "message" => "Paid at invalid, should be in format Y-m-d H:i:s"
                                ]
                            ], $expense->getAllAttributesDateAndFormat());

        $this->assertIsArray($expense->jsonSerialize());
        $this->assertNotEmpty($expense->getDateTimeStringFrom('created_at'));
        $this->assertEmpty($expense->getDateTimeStringFrom('updated_at'));
        $this->assertIsArray($expense->getNameAndIdUser('user'));
        $this->assertEmpty($expense->getFullData()["updated_at"]);
        $this->assertEmpty($expense->getDeletedAt());

    }

    public function testIfExpenseIsPartOfAGroup()
    {
        $expense = new Expense();
        $this->assertEquals(false, $expense->thisExpenseIsPartOfGroup());
        $this->assertIsString($expense->getGeneratedTokenInstallmentGroupUsing(new Bin2HexGenerate()));
        $this->assertEquals(true, $expense->thisExpenseIsPartOfGroup());
    }

    public function testGetFullDataReturnedDefault()
    {
        $expense = new Expense();

        $res = $expense->getFullData();

        $this->assertCount(19, $res);
        $this->assertIsArray($res);
        $this->assertEmpty($res["id"]);
        $this->assertEmpty($res["place"]);
        $this->assertNull($res["description"]);
        $this->assertEmpty($res["registered_by"]);
        $this->assertEmpty($res["value"]);
        $this->assertEmpty($res["amount_paid"]);
        $this->assertEquals("cash", $res["payment_type"]);
        $this->assertEquals("pending", $res["status"]);
        $this->assertEquals("pendente", $res["status_description"]);
        $this->assertEquals("dinheiro", $res["payment_type_description"]);
        $this->assertEquals(1, $res["installment_number"]);
        $this->assertEquals(1, $res["total_installments"]);
        $this->assertEmpty($res["paid_by"]);
        $this->assertEmpty($res["paid_at"]);
        $this->assertEmpty($res["due_date"]);
        $this->assertNotEmpty($res["created_at"]);
        $this->assertEmpty($res["updated_at"]);
        $this->assertEmpty($res["token_installment_group"]);

    }

    /**
     * @throws \Exception
     */
    public function testChangeExpense()
    {
        $expense = new Expense();

        $this->assertEmpty($expense->getDateTimeStringFrom('updated_at'));
        $expense->updateLastUpdated();

        $this->assertNotEmpty($expense->getDateTimeStringFrom('updated_at'));

        $fullData = $expense->getFullData();

        $this->assertEmpty($fullData["registered_by"]);
        $this->assertEmpty($fullData["paid_by"]);
    }

    public function testSetTotalInstallments3ShouldDivideValueBy3()
    {
        $expense = new Expense();
        $data = [
            "total_installments" => 3,
            "value" => 3000
        ];

        $expense->setAttributes($data);

        $original = $expense->getOriginalData();
        $this->assertEquals(1, $original["installment_number"]);
        $this->assertEquals(3000, $original["value"]);
    }

    public function testValueShouldBeFloat()
    {
        $expense = new Expense();

        $this->assertIsFloat($expense->getValue());
        $this->assertEquals(0, $expense->getValue());
    }

    public function testFixTotalInstallmentIfCashShouldSetToOne()
    {
        $expense = new Expense();
        $data = [
            "total_installments" => 2
        ];

        $expense->setAttributes($data);

        $expenseData = $expense->getOriginalData();

        $this->assertEquals(2, $expenseData["total_installments"]);

        $expense->fixTotalInstallmentIfCash();

        $expenseData = $expense->getOriginalData();

        $this->assertEquals(1, $expenseData["total_installments"]);
    }

    public function testCategoryInfoShouldReturnNull()
    {
        $expense = new Expense();

        $this->assertNull($expense->getCategoryDetails());
    }

    public function testCategoryInfoShouldReturnDetails()
    {
        $category = new Category();
        $category->setAttributes(["description" => 'my cat']);
        $expense = new Expense();
        $expense->setAttributes(["category" => $category]);

        $this->assertIsArray($expense->getCategoryDetails());
        $categoryDetails = $expense->getCategoryDetails();

        $this->assertEquals($categoryDetails["description"], "my cat");
    }

    public function testSetPaidByShouldSuccess()
    {
        $user = new User();
        $user->setAttributes(["name" => 'Homer Simpson']);

        $expense = new Expense();
        $expense->setAttributes(["paid_by" => $user]);

        $expenseData = $expense->getFullData();

        $this->assertNotNull($expenseData["paid_by"]);

        $userData = $expenseData["paid_by"];

        $this->assertEquals('Homer Simpson', $userData["name"]);
    }

    public function testCreditCardDetailsShouldBeNull()
    {
        $expense = new Expense();
        $this->assertIsArray($expense->getCreditCardDetails());
    }

    public function testCreditCardDetailsShouldReturnDetails()
    {
        $creditCard = new CreditCard();
        $creditCard->setAttributes(["description" => "my card"]);

        $expense = new Expense();
        $expense->setAttributes(["creditCard"=>$creditCard]);

        $this->assertIsArray($expense->getCreditCardDetails());
        $creditCardDetails = $expense->getCreditCardDetails();

        $this->assertEquals("my card", $creditCardDetails["credit_card"]["description"]);
    }

    public function testThisExpenseIsPartOfGroupShouldFalse()
    {
        $expense = new Expense();

        $this->assertFalse($expense->thisExpenseIsPartOfGroup());;
    }
}