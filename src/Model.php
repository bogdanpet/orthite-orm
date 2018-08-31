<?php


namespace Orthite\ORM;


use Orthite\Database\Database;

abstract class Model
{
    /**
     * Holds the related table name.
     *
     * @var null|string
     */
    protected $table = null;

    /**
     * Table's primary key column.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Writable columns.
     *
     * @var array
     */
    protected $writable = [];

    /**
     * Hidden columns.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Holds fetched properties.
     *
     * @var array
     */
    protected $props = [];

    /**
     * Holds database instance.
     *
     * @var Database
     */
    protected $db;

    public function __construct()
    {
        $this->setTable();

        // Temporary db
        $this->db = new Database([
            'user' => 'root',
            'password' => '',
            'database' => 'orthite_test'
        ]);
    }

    protected static function newInstance()
    {
        $model = static::class;

        return new $model;
    }

    protected function setTable()
    {
        if (empty($this->table)) {
            $table = preg_replace('/.*\\\\/', '', static::class);

            $this->table = plural(strtolower($table));
        }
    }

    public static function find($key) {
        $model = static::newInstance();

        $data = $model->db->where($model->primaryKey, $key)->select($model->table);

        foreach ($data[0] as $key => $value) {
            $model->$key = $value;
        }

        return $model;
    }

    public static function all()
    {
        $model = static::newInstance();

        $collection = new Collection();

        $data = $model->db->select($model->table);

        foreach ($data as $row) {
            foreach ($row as $key => $value) {
                $model->$key = $value;
            }

            $collection->add($model);
        }

        return $collection;
    }

    public function insert() {
        $data = [];

        foreach ($this->props as $column => $value) {
            if (in_array($column, $this->writable)) {
                $data[$column] = $value;
            }
        }

        return $this->db->insert($this->table, $data);
    }

    public function update()
    {
        $data = [];

        foreach ($this->props as $column => $value) {
            if (in_array($column, $this->writable)) {
                $data[$column] = $value;
            }
        }

        return $this->db->where($this->primaryKey, $this->props[$this->primaryKey])
                        ->update($this->table, $data);
    }

    public function __get($name)
    {
        if (isset($this->props[$name])) {
            return $this->props[$name];
        }

        return null;
    }

    public function __set($name, $value)
    {
        $this->props[$name] = $value;
    }
}