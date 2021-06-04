<?php


namespace App\Repositories;


use App\Models\Group;

/**
 * Class GroupRepository
 * @package App\Repositories
 */
class GroupRepository extends BaseRepository
{
    /**
     * GroupRepository constructor.
     */
    public function __construct()
    {
        parent::__construct(Group::$NODE_TYPE, Group::class);
    }

    /**
     * @param array $group
     * @return int
     * @throws \Everyman\Neo4j\Exception
     */
    public function findOrCreate(array $group)
    {
        $node = null;

        if (!empty($group['internalId'])) {
            $node = $this->getByInternalId($group['internalId']);
        }

        if (!empty($node)) {
            return $node->getId();
        }

        return $this->store($group);
    }
}
