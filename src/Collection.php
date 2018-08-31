<?php


namespace Orthite\ORM;


use Traversable;

class Collection implements \IteratorAggregate
{

    protected $models = [];

    protected $total = 0;

    protected $limit = 10;

    protected $chunk = null;

    public function add(Model $model)
    {
        $this->models[] = $model;
        $this->total++;
    }

    public function get($key)
    {
        return $this->models[$key];
    }

    public function all()
    {
        return $this->models;
    }

    public function filterBy($column, $value)
    {
        $this->models = array_filter($this->models, function($model) use ($column, $value) {
            return $model->$column == $value;
        });

        return $this;
    }

    public function setChunk($chunk, $limit, $count)
    {
        $this->chunk = $chunk;
        $this->limit = $limit;
        $this->total = $count;
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->models);
    }

    public function __call($name, $arguments)
    {
        if (substr($name, 0, 8) == 'filterBy') {
            $column = str_replace('filterBy', '', $name);
            $column = preg_split('/(?=[A-Z])/', $column, -1, PREG_SPLIT_NO_EMPTY);
            $column = implode('_', $column);
            $column = strtolower($column);

            return $this->filterBy($column, ...$arguments);
        }

        return null;
    }

    public function toArray($withHidden = false)
    {
        $data = [];
        $total = $this->total;
        $chunk = $this->chunk;
        $from = $chunk == null ? 1 : 1 + ($chunk - 1) * $this->limit;
        $to = $chunk == null ? $total : $chunk * $this->limit;

        foreach ($this->models as $model) {
            $data[] = $model->toArray($withHidden);
        }

        return compact('data', 'total', 'chunk', 'from', 'to');
    }

    public function toJson($withHidden = false)
    {
        return json_encode($this->toArray($withHidden), JSON_PRETTY_PRINT);
    }
}