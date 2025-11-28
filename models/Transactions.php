<?php
require_once __DIR__ . '/Model.php';

class Transactions extends Model
{
    protected static $table = 'transaction';
    protected static $primaryKey = 'id';

    public $id;
    public $name;
    public $category;
    public $amount;
    public $date_created;
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public static function setConnection($conn)
    {
        parent::setConnection($conn);
    }

    public static function all()
    {
        $results = parent::all();
        return $results ? array_map(fn($row) => new self($row), $results) : null;
    }

    public static function find($id)
    {
        $row = parent::find($id);
        return $row ? new self($row) : null;
    }
    public static function create(array $data)
    {
        $result = parent::create($data);

        return $result
            ? new self($result)
            : null;
    }

    public function update(array $data)
    {
        $result = parent::updateById($this->id, $data);
        if ($result) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
            return $this;
        }
        return false;
    }

    public function delete()
    {
        return parent::deleteById($this->id);
    }




}
