<?php


namespace Orthite\ORM;


abstract class Model
{
    /**
     * Holds the related table name.
     *
     * @var null|string
     */
    protected $table = null;

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

    public function __construct()
    {
        $this->setTable();
    }

    protected function setTable()
    {
        if (empty($this->table)) {
            $table = preg_replace('/.*\\\\/', '', static::class);

            $this->table = plural(strtolower($table));
        }
    }
}