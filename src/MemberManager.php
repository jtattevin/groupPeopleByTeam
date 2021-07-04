<?php


namespace App;


/**
 * Class MemberManager
 *
 * @package App
 */
class MemberManager
{

    /**
     * @var LinkManager
     */
    private $linkManager;

    /**
     * @var array
     */
    private $members = [];

    /**
     * MemberManager constructor.
     *
     * @param LinkManager $linkManager
     */
    public function __construct(LinkManager $linkManager)
    {

        $this->linkManager = $linkManager;
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
     * Add preferences
     *
     * @param string $member
     * @param array  $preferences
     *
     * @return void
     */
    public function addPreferences(string $member, array $preferences): void
    {
        // If the team size is 4, the first one is weighted 3 points, the second 2 points, etc
        $maxScore = count($preferences);
        foreach ($preferences as $position => $preference) {
            $this->linkManager->link(
                $member,
                $preference,
                $maxScore - $position
            );
        }

        // For every other member, we put the weight down by one.
        // This will put an advantage to the case were both user choose the other
        foreach ($this->members as $anotherMember) {
            // We don't link to the current user
            if ($anotherMember == $member) {
                continue;
            }

            // We don't put a weight down if it's a chosen user
            if (in_array($anotherMember, $preferences)) {
                continue;
            }

            $this->linkManager->link(
                $member,
                $anotherMember,
                -1
            );

        }
    }
}
