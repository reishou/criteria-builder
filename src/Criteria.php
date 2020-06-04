<?php

namespace Reishou\Criteria;

use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class Criteria
{
    /** @var string $table */
    protected $table;

    /** @var array $param */
    protected $param = [];

    /** @var array $original */
    protected $original = [];

    /** @var array $criteria */
    protected $criteria = [];

    /** @var string $prefixMethod */
    protected $prefixMethod = 'criteria';

    /**
     * Criteria constructor.
     *
     * @param array $param
     */
    public function __construct(array $param = [])
    {
        $param = $param ?: request()->query();
        $this->setOriginal($param);
        $this->setParam($param);
    }

    /**
     * @param array $param
     */
    public function setOriginal(array $param)
    {
        $this->original = $param;
    }

    /**
     * @param array $param
     */
    public function setParam(array $param)
    {
        $this->param = collect($param)
            ->transform($this->transformParam())
            ->filter($this->filterParam())
            ->filter($this->filterCriteria())
            ->toArray();
    }

    /**
     * @return Closure
     */
    protected function transformParam()
    {
        return function ($value) {
            if (is_array($value)) {
                $value = collect($value)->filter(
                    function ($value) {
                        return $value !== null;
                    }
                )
                    ->toArray();
            }

            return $value;
        };
    }

    /**
     * @return Closure
     */
    protected function filterParam()
    {
        return function ($value) {
            if (is_object($value)) {
                return false;
            }

            return is_array($value) ? !empty($value) : $value !== null;
        };
    }

    /**
     * @return Closure
     */
    protected function filterCriteria()
    {
        return function ($value, $key) {
            $criteria = $this->getCriteria();

            return in_array($key, $criteria) || key_exists($key, $criteria);
        };
    }

    /**
     * @param Builder|\Illuminate\Database\Eloquent\Builder $query
     */
    public function apply($query)
    {
        $this->setTable($query);
        foreach ($this->param as $key => $value) {
            if ($this->customMethod($query, $key, $value)) {
                continue;
            }
            $this->basicCriteria($query, $key, $value);
        }
    }

    /**
     * @param $query
     */
    protected function setTable($query)
    {
        $this->table = DB::getTablePrefix();
        if ($query instanceof \Illuminate\Database\Eloquent\Builder) {
            $this->table .= $query->getModel()->getTable();
        } elseif ($query instanceof Builder) {
            $this->table .= $query->from;
        }
    }

    /**
     * @param Builder|\Illuminate\Database\Eloquent\Builder $query
     * @param $key
     * @param $value
     * @return bool
     */
    protected function customMethod($query, $key, $value)
    {
        $method = $this->prefixMethod . Str::studly($key);
        if (method_exists($this, $method)) {
            $this->{$method}($query, $value);

            return true;
        }

        return false;
    }

    /**
     * @param Builder|\Illuminate\Database\Eloquent\Builder $query
     * @param $key
     * @param $value
     */
    protected function basicCriteria($query, $key, $value)
    {
        $criteria = $this->getCriteria();

        if (in_array($key, $criteria)) {
            $value = is_array($value) ? $value : [$value];
            $query->whereIn($this->getTable() . '.' . $key, $value);

            return;
        }

        if (key_exists($key, $criteria) && Str::lower($criteria[$key]) === 'like') {
            $query->where($this->getTable() . '.' . $key, 'like', "%$value%");

            return;
        }
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return array
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * @return array
     */
    public function getCriteria()
    {
        return $this->criteria;
    }
}
