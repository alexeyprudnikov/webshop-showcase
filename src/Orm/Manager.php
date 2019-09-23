<?php
/**
 * Created by PhpStorm.
 * User: aprudnikov
 * Date: 2019-08-02
 * Time: 12:24
 */

namespace App\Orm;

use SleekDB\SleekDB;

class Manager
{
    protected $storage;

    /**
     * Manager constructor.
     * @param SleekDB $storage
     */
    public function __construct(SleekDB $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param int $id
     * @param array $data
     * @throws \Exception
     */
    public function update(int $id, array $data): void
    {
        $this->storage->where('_id', '=', $id)->update($data);
    }

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function insert(array $data): array
    {
        return $this->storage->insert($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @throws \Exception
     */
    public function delete(int $id): void
    {
        $this->storage->where('_id', '=', $id)->delete();
    }
}