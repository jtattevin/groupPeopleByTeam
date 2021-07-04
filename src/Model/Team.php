<?php


namespace App\Model;


/**
 * Class Team
 *
 * @package App\Model
 */
class Team
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $members = [];

    /**
     * @var int
     */
    private $teamSize;

    /**
     * Team constructor.
     *
     * @param string $name
     * @param int    $teamSize
     */
    public function __construct(string $name, int $teamSize)
    {
        $this->name     = $name;
        $this->teamSize = $teamSize;
    }

    /**
     * Add member
     *
     * @param string $member
     *
     * @return void
     */
    public function addMember(string $member): void
    {
        $this->members[] = $member;
    }

    /**
     * Contain member
     *
     * @param string $member
     *
     * @return bool
     */
    public function containMember(string $member): bool
    {
        return in_array($member, $this->members);
    }

    /**
     * Is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return count($this->members) == 0;
    }

    /**
     * Is full
     *
     * @return bool
     */
    public function isFull(): bool
    {
        return count($this->members) == $this->teamSize;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get members
     *
     * @return array
     */
    public function getMembers(): array
    {
        return $this->members;
    }
}
