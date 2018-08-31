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

        return $model->make($data[0]);
    }

    public static function findBy($column, $value)
    {
        $model = static::newInstance();

        $data = $model->db->where($column, $value)->select($model->table);

        if (count($data) === 1) {
            return $model->make($data[0]);
        }

        return $model->makeCollection($data);
    }

    public static function all()
    {
        $model = static::newInstance();

        $data = $model->db->select($model->table);

        return $model->makeCollection($data);
    }

    public static function chunk($limit = 10, $chunk = null, $paging = 'page')
    {
        $page = !empty($_GET[$paging]) ? $_GET[$paging] : 1;
        $chunk = $chunk == null ? $page : $chunk;

        $model = static::newInstance();

        $data = $model->db->limit($limit, $chunk)->select($model->table);

        $collection = $model->makeCollection($data);

        $count = static::count();

        $collection->setChunk($chunk, $limit, $count);

        return $collection;
    }

    public static function count()
    {
        $model = static::newInstance();

        $stmt = $model->db->execute('SELECT COUNT(*) as cnt FROM `' . $model->table . '`;');

        $result = $stmt->fetch();

        return (int)$result['cnt'];
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

    public function save()
    {
        if (isset($this->props[$this->primaryKey])) {
            return $this->update();
        }

        return $this->insert();
    }

    public function make($data) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    public static function create($data)
    {
        return static::newInstance()->make($data)->insert();
    }

    public function makeCollection($data)
    {
        if (empty($data)) {
            return null;
        }

        $collection = new Collection();

        foreach ($data as $row) {
            $model = static::newInstance();
            foreach ($row as $key => $value) {
                $model->$key = $value;
            }

            $collection->add($model);
        }

        return $collection;
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

    public function toArray($withHidden = false)
    {
        $props = [];
        $hidden = [];
        foreach ($this->props as $key => $value) {
            if (!in_array($key, $this->hidden)) {
                $props[$key] = $value;
            } else {
                $hidden[$key] = $value;
            }
        }
        return $withHidden ? array_merge($props, $hidden) : $props;
    }

    public function toJson($withHidden = false)
    {
        return json_encode($this->toArray($withHidden), JSON_PRETTY_PRINT);
    }
}