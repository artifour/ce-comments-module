<?php

namespace Deti123\Comment\Application\ValueObject;

class CommentUsername
{
    public const VALID_PATTERN = '/^[ёа-яa-z_()*!@\d\s]{1,80}$/iu';

    private $username;

    public function __construct($username)
    {
        $this->username = $username;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        if (!is_string($this->username)) {
            return false;
        }

        if (trim($this->username) === '') {
            return false;
        }

        if (!preg_match(self::VALID_PATTERN, $this->username)) {
            return false;
        }

        return true;
    }

    public function __toString()
    {
        return $this->username;
    }
}
