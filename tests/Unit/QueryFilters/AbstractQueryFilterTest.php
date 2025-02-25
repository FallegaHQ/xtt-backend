<?php

namespace Tests\Unit\QueryFilters;

use App\QueryFilters\AbstractQueryFilter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AbstractQueryFilterTest extends TestCase{

    protected Builder             $queryBuilder;
    protected AbstractQueryFilter $queryFilter;
    protected string              $tableName      = 'test_table';
    protected array               $fillableFields = [
        'name',
        'description',
        'date',
        'is_active',
    ];

    #[Test]
    public function shouldReturnBuilderWithoutFilters(): void{
        $this->queryBuilder->shouldNotReceive('where');

        $result = $this->queryFilter->apply($this->queryBuilder, []);
        $this->assertSame($this->queryBuilder, $result);
    }

    #[Test]
    public function shouldApplyIgnoresNonFillableFields(): void{
        $this->queryBuilder->shouldNotReceive('where');

        $result = $this->queryFilter->apply($this->queryBuilder, ['non_fillable_field' => 'value']);
        $this->assertSame($this->queryBuilder, $result);
    }

    #[Test]
    public function shouldApplyBooleanFilter(): void{
        $this->queryBuilder->shouldReceive('where')
                           ->once()
                           ->with("{$this->tableName}.is_active", true)
                           ->andReturnSelf();

        $result = $this->queryFilter->apply($this->queryBuilder, ['is_active' => '1']);
        $this->assertSame($this->queryBuilder, $result);
    }

    #[Test]
    public function shouldApplyLikeFilter(): void{
        $this->queryBuilder->shouldReceive('where')
                           ->once()
                           ->with("{$this->tableName}.description", 'LIKE', "%test%")
                           ->andReturnSelf();

        $result = $this->queryFilter->apply($this->queryBuilder, ['description' => 'test']);
        $this->assertSame($this->queryBuilder, $result);
    }

    #[Test]
    public function shouldApplyArrayFilter(): void{
        $this->queryBuilder->shouldReceive('whereIn')
                           ->once()
                           ->with(
                               'name',
                               [
                                   'value1',
                                   'value2',
                               ],
                           )
                           ->andReturnSelf();

        $result = $this->queryFilter->apply(
            $this->queryBuilder,
            [
                'name' => [
                    'value1',
                    'value2',
                ],
            ],
        );

        $this->assertSame($this->queryBuilder, $result);
    }

    #[Test]
    public function shouldApplyExactDateFilter(): void{
        $date = '2024-02-08';

        $this->queryBuilder->shouldReceive('whereDate')
                           ->once()
                           ->withAnyArgs()
                           ->withSomeOfArgs("{$this->tableName}.date")
                           ->andReturnSelf();

        $result = $this->queryFilter->apply($this->queryBuilder, ['date' => $date]);
        $this->assertSame($this->queryBuilder, $result);
    }

    #[Test]
    public function shouldApplyDateRangeFilter(): void{
        $start = '2024-01-01';
        $end   = '2024-02-08';

        $this->queryBuilder->shouldReceive('whereBetween')
                           ->once()
                           ->with(
                               "{$this->tableName}.date",
                               [
                                   Carbon::parse($start)
                                         ->startOfDay(),
                                   Carbon::parse($end)
                                         ->endOfDay(),
                               ],
                           )
                           ->andReturnSelf();

        $result = $this->queryFilter->apply($this->queryBuilder, [
            'date' => [
                'start' => $start,
                'end'   => $end,
            ],
        ]);

        $this->assertSame($this->queryBuilder, $result);
    }

    #[Test]
    public function shouldApplyDateAfterFilter(): void{
        $date       = '2024-01-01';
        $carbonDate = Carbon::parse($date);

        $this->queryBuilder->shouldReceive('whereDate')
                           ->once()
                           ->withSomeOfArgs("{$this->tableName}.date", '>=')
                           ->andReturnSelf();

        $result = $this->queryFilter->apply($this->queryBuilder, ['date' => "after:{$date}"]);
        $this->assertSame($this->queryBuilder, $result);
    }

    #[Test]
    public function shouldApplyDateBeforeFilter(): void{
        $date       = '2024-02-08';
        $carbonDate = Carbon::parse($date);

        $this->queryBuilder->shouldReceive('whereDate')
                           ->once()
                           ->withSomeOfArgs("{$this->tableName}.date", '<=')
                           ->andReturnSelf();

        $result = $this->queryFilter->apply($this->queryBuilder, ['date' => "before:{$date}"]);
        $this->assertSame($this->queryBuilder, $result);
    }

    #[Test]
    public function shouldApplyDateOnFilter(): void{
        $date       = '2024-02-08';
        $carbonDate = Carbon::parse($date);

        $this->queryBuilder->shouldReceive('whereDate')
                           ->once()
                           ->withSomeOfArgs("$this->tableName.date")
                           ->andReturnSelf();

        $result = $this->queryFilter->apply($this->queryBuilder, ['date' => "on:$date"]);
        $this->assertSame($this->queryBuilder, $result);
    }

    #[Test]
    public function shouldApplyMultipleDateOperators(): void{
        $start = '2024-01-01';
        $end   = '2024-02-08';

        $this->queryBuilder->shouldReceive('whereDate')
                           ->once()
                           ->ordered("{$this->tableName}.date")
                           ->withSomeOfArgs("$this->tableName.date", '<=')
                           ->andReturnSelf();

        $this->queryBuilder->shouldReceive('whereDate')
                           ->once()
                           ->ordered("{$this->tableName}.date")
                           ->withSomeOfArgs("$this->tableName.date", '>=')
                           ->andReturnSelf();

        $result = $this->queryFilter->apply($this->queryBuilder, [
            'date' => [
                'after'  => $start,
                'before' => $end,
            ],
        ]);

        $this->assertSame($this->queryBuilder, $result);
    }

    protected function setUp(): void{
        parent::setUp();

        // Create a mock query builder
        $this->queryBuilder = Mockery::mock(Builder::class);

        // Create a concrete implementation of the abstract class for testing
        $this->queryFilter = new class($this->tableName, $this->fillableFields) extends AbstractQueryFilter{
            protected array $dateFilterFields = ['date'];
            protected array $likeFilterFields = ['description'];
            protected array $boolFilterFields = ['is_active'];
        };
    }

}
