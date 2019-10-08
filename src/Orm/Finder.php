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

    protected $storage;

    protected $offset = 0;

    protected $limit = 40;

    protected $orderBy = 'desc:_id';

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
     */
    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @param string $orderBy
     */
    public function setOrderBy(string $orderBy): void
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param string $orderBy
     * @return array|mixed|null
     * @throws \Exception
     */
    public function findAll()
    {
        [$order, $ordBy] = explode(':', $this->orderBy);
        return $this->storage
            ->skip($this->offset)
            ->limit($this->limit)
            ->orderBy($order, $ordBy)
            ->fetch();
    }

    /**
     * @param int $id
     * @return array|mixed|null
     * @throws \Exception
     */
    public function findByCategoryId(int $id)
    {
        [$order, $ordBy] = explode(':', $this->orderBy);
        return $this->storage
            ->where('category', '=', $id)
            ->skip($this->offset)
            ->limit($this->limit)
            ->orderBy($order, $ordBy)
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
     * @param string $ids
     * @return array|mixed|null
     * @throws \Exception
     */
    public function findByIds(string $ids)
    {
        $idArray = explode(',', $ids);
        return $this->storage->in('_id', $idArray)->fetch();
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