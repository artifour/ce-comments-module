<?php

namespace ProjectName\Comment\Domain\Entity;

use Core\Domain\Entity\EntityInterface;
use ProjectName\Comment\Domain\ValueObject\CommentEntity;
use ProjectName\Comment\Domain\ValueObject\CommentRelatedTo;
use ProjectName\Comment\Domain\ValueObject\CommentStatus;

class Comment implements EntityInterface
{
    public const TABLE_NAME = 'comment';

    public const COLUMN_ENTITY = 'entity';
    public const COLUMN_ENTITY_ID = 'entityId';
    public const COLUMN_CREATED_BY = 'createdBy';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_CREATED_AT = 'createdAt';

    public const ATTR_ID = self::TABLE_NAME . '.id';
    public const ATTR_ENTITY = self::TABLE_NAME . '.' . self::COLUMN_ENTITY;
    public const ATTR_ENTITY_ID = self::TABLE_NAME . '.' . self::COLUMN_ENTITY_ID;
    public const ATTR_CONTENT = self::TABLE_NAME . '.content';
    public const ATTR_PARENT_ID = self::TABLE_NAME . '.parentId';
    public const ATTR_LEVEL = self::TABLE_NAME . '.level';
    public const ATTR_CREATED_BY = self::TABLE_NAME . '.' . self::COLUMN_CREATED_BY;
    public const ATTR_UPDATED_BY = self::TABLE_NAME . '.updatedBy';
    public const ATTR_STATUS = self::TABLE_NAME . '.' . self::COLUMN_STATUS;
    public const ATTR_CREATED_AT = self::TABLE_NAME . '.' . self::COLUMN_CREATED_AT;
    public const ATTR_UPDATED_AT = self::TABLE_NAME . '.updatedAt';
    public const ATTR_IP_ADDRESS = self::TABLE_NAME . '.ipAddress';

    /** @var int */
    public $id;

    /**
     * @var string
     * @see CommentEntity
     */
    public $entity;

    /** @var int */
    public $entityId;

    /** @var string */
    public $content;

    /** @var int reference comment */
    public $parentId;

    /** @var int */
    public $level;

    /** @var int reference user */
    public $createdBy;

    /** @var int reference user */
    public $updatedBy;

    /**
     * @var string
     * @see CommentRelatedTo
     */
    public $relatedTo;

    /** @var string */
    public $url;

    /**
     * @var int
     * @see CommentStatus
     */
    public $status;

    /** @var int */
    public $createdAt;

    /** @var int */
    public $updatedAt;

    /** @var string */
    public $email;

    /** @var string */
    public $name;

    /** @var string */
    public $ipAddress;

    /**
     * @inheritDoc
     */
    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}
