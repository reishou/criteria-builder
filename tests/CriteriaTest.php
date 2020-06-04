<?php

namespace Reishou\Criteria\Tests;

use Illuminate\Support\Facades\DB;

class CriteriaTest extends TestCase
{
    /** @test */
    public function criteria_get_correct_table_from_query_builder()
    {
        $criteria = new UserCriteria();
        $query    = DB::table('users');
        $criteria->apply($query);
        $table = $criteria->getTable();

        $this->assertEquals('users', $table);
    }

    /** @test */
    public function criteria_get_correct_table_from_eloquent_builder()
    {
        $criteria = new UserCriteria();
        $query    = User::query();
        $criteria->apply($query);
        $table = $criteria->getTable();

        $this->assertEquals('users', $table);
    }

    /** @test */
    public function criteria_make_correct_sql_with_query_builder()
    {
        $param = ['status' => 0, 'name' => 'Reishou'];

        $basicQuery = DB::table('users')
            ->whereIn('users.status', [$param['status']])
            ->where('users.name', 'like', '%' . $param['name'] . '%');

        $criteria      = new UserCriteria($param);
        $criteriaQuery = DB::table('users');
        $criteria->apply($criteriaQuery);

        $this->assertEqualQueries($basicQuery, $criteriaQuery);
    }

    /** @test */
    public function criteria_make_correct_sql_with_eloquent_builder()
    {
        $param = ['status' => 0, 'name' => 'Reishou'];

        $basicQuery = User::query()
            ->whereIn('users.status', [$param['status']])
            ->where('users.name', 'like', '%' . $param['name'] . '%');

        $criteria      = new UserCriteria($param);
        $criteriaQuery = User::query();
        $criteria->apply($criteriaQuery);

        $this->assertEqualQueries($basicQuery, $criteriaQuery);
    }

    /**
     * @test
     * @dataProvider provideMeaningfulValue
     * @param array $param
     * @param array $expected
     */
    public function criteria_only_apply_meaningful_value(array $param, array $expected)
    {
        $criteria  = new UserCriteria($param);
        $transform = $criteria->getParam();

        $this->assertEqualsCanonicalizing($expected, $transform);
    }

    /**
     * @return array|\array[][]
     */
    public function provideMeaningfulValue(): array
    {
        return [
            "value null"                 => [
                ['status' => null, 'name' => false, 'age' => 0],
                ['name' => false, 'age' => 0],
            ],
            "array with all value null"  => [
                ['status' => [null, null, null], 'name' => false, 'age' => 0],
                ['name' => false, 'age' => 0],
            ],
            "array with some value null" => [
                ['status' => [null, null, 0], 'name' => false, 'age' => 0],
                ['status' => [0], 'name' => false, 'age' => 0],
            ],
            "object of string"           => [
                ['status' => (object)'laravel', 'name' => false, 'age' => 0],
                ['name' => false, 'age' => 0],
            ],
            "object of array"            => [
                ['status' => (object)['laravel'], 'name' => false, 'age' => 0],
                ['name' => false, 'age' => 0],
            ],
        ];
    }

    protected function assertEqualQueries($expected, $actual)
    {
        $this->assertEquals($expected->toSql(), $actual->toSql());
        $this->assertEquals($expected->getBindings(), $actual->getBindings());
    }
}
