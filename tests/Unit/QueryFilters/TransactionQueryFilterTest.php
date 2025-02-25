<?php

namespace QueryFilters;

use App\QueryFilters\TransactionQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

class TransactionQueryFilterTest extends TestCase{
    protected Builder                $queryBuilder;
    protected TransactionQueryFilter $queryFilter;
    protected string                 $tableName      = 'transactions';
    protected array                  $fillableFields = [
        'user_id',
        'type',
        'amount',
        'description',
        'date',
    ];

    #[Test]
    public function shouldTransactionQueryFilterHasCorrectProperties(): void{
        // Use reflection to check protected properties
        $reflection = new ReflectionClass($this->queryFilter);

        $dateFilterFieldsProp = $reflection->getProperty('dateFilterFields');
        $dateFilterFieldsProp->setAccessible(true);
        $this->assertEquals(['date'], $dateFilterFieldsProp->getValue($this->queryFilter));

        $likeFilterFieldsProp = $reflection->getProperty('likeFilterFields');
        $likeFilterFieldsProp->setAccessible(true);
        $this->assertEquals(['description'], $likeFilterFieldsProp->getValue($this->queryFilter));

        $boolFilterFieldsProp = $reflection->getProperty('boolFilterFields');
        $boolFilterFieldsProp->setAccessible(true);
        $this->assertEquals([], $boolFilterFieldsProp->getValue($this->queryFilter));
    }

    #[Test]
    public function shouldApplyDescriptionFilter(): void{
        $this->queryBuilder->expects($this->once())
                           ->method('where')
                           ->with("{$this->tableName}.description", 'LIKE', "%groceries%")
                           ->willReturn($this->queryBuilder);

        $result = $this->queryFilter->apply($this->queryBuilder, ['description' => 'groceries']);
        $this->assertSame($this->queryBuilder, $result);
    }

    #[Test]
    public function shouldApplyTypeFilter(): void{
        $this->queryBuilder->expects($this->once())
                           ->method('where')
                           ->with('type', 'expense')
                           ->willReturn($this->queryBuilder);

        $result = $this->queryFilter->apply($this->queryBuilder, ['type' => 'expense']);
        $this->assertSame($this->queryBuilder, $result);
    }

    protected function setUp(): void{
        parent::setUp();

        // Create a mock query builder
        $this->queryBuilder = $this->getMockBuilder(Builder::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();

        // Create the transaction query filter
        $this->queryFilter = new TransactionQueryFilter($this->tableName, $this->fillableFields);
    }
}