<?php
/**
 * Created by PhpStorm.
 * User: aprudnikov
 * Date: 2019-08-02
 * Time: 12:12
 */

namespace App\Orm;

use SleekDB\SleekDB;

class Finder
{
    protected CONST ItemsPerPage = 16;

    protected $storage;

    /**
     * Finder constructor.
     * @param SleekDB $storage
     */
    public function __construct(SleekDB $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param string $orderBy
     * @return array|mixed|null
     * @throws \Exception
     */
    public function findAll(int $offset = 0, $limit = self::ItemsPerPage, $orderBy = 'desc:_id')
    {
        [$order, $ordBy] = explode(':', $orderBy);
        return $this->storage
            ->skip($offset)
            ->limit($limit)
            ->orderBy( $order, $ordBy )
            ->fetch();
    }

    /**
     * @param int $id
     * @return array|mixed|null
     * @throws \Exception
     */
    public function findById(int $id)
    {
        $response = $this->storage->where('_id', '=', $id)->fetch();
        return count($response) > 0 ? $response[0] : null;
    }

    /**
     * @param int $id
     * @return array|mixed|null
     * @throws \Exception
     */
    public function findByCategoryId(int $id)
    {
        return $this->storage->where('category', '=', $id)->fetch();
    }

    /**
     * @param string $hash
     * @return array|mixed|null
     * @throws \Exception
     */
    public function findByHash(string $hash)
    {
        $response = $this->storage->where('hash', '=', $hash)->fetch();
        return count($response) > 0 ? $response[0] : null;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function count(): int
    {
        $items = $this->findAll();
        return is_array($items) ? count($items) : 0;
    }
}