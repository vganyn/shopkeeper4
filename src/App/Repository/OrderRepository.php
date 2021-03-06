<?php

namespace App\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ORM\ORMInvalidArgumentException;

/**
 * OrderRepository
 */
class OrderRepository extends BaseRepository
{

    /**
     * @param $id
     * @param $userId
     * @return object
     */
    public function getOneByUser($id, $userId)
    {
        if (!$userId) {
            throw new ORMInvalidArgumentException('User ID is not set.');
        }
        return $this->findOneBy([
            'id' => $id,
            'userId' => $userId
        ]);
    }

    /**
     * @param $userId
     * @param int $skip
     * @param int $limit
     * @return \Doctrine\ODM\MongoDB\Cursor
     */
    public function getAllByUserId($userId, $skip = 0, $limit = 100)
    {
        if (!$userId) {
            throw new ORMInvalidArgumentException('User ID is not set.');
        }

        $query = $this->createQueryBuilder();

        $query = $query
            ->field('userId')->equals($userId)
            ->sort('createdDate', 'desc')
            ->skip($skip)
            ->limit($limit);

        return $query->getQuery()->execute();
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function getCountByUserId($userId)
    {
        if (!$userId) {
            throw new ORMInvalidArgumentException('User ID is not set.');
        }
        $query = $this->createQueryBuilder();
        return $total = $query
            ->field('userId')->equals($userId)
            ->getQuery()
            ->execute()
            ->count();
    }

}
