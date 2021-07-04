<?php


namespace App;


use App\Model\Link;
use App\Model\Team;

/**
 * Class TeamManager
 *
 * @package App
 */
class TeamManager
{

    /**
     * @var Team[]
     */
    private $teams = [];

    /**
     * @var Link[]
     */
    private $skippedLinks = [];

    /**
     * TeamManager constructor.
     *
     * @param int $teamAmount
     * @param int $teamSize
     */
    public function __construct(int $teamAmount, int $teamSize)
    {
        foreach (range(1, $teamAmount) as $teamId) {
            $this->teams[] = new Team("team_" . $teamId, $teamSize);
        }
    }

    /**
     * Digest link
     *
     * Try to put the two user in the same team if possible
     * If not, they will be put on hold and we will retry when something change
     *
     * @param Link $link
     *
     * @return void
     */
    public function digestLink(Link $link): void
    {
        printf("Digesting link between %s and %s : %d\n",
            $link->getMemberA(), $link->getMemberB(), $link->getWeight()
        );
        $memberAIsInATeam = $this->memberIsInATeam($link->getMemberA());
        $memberBIsInATeam = $this->memberIsInATeam($link->getMemberB());

        // Build a string to handle the case in a switch case instead of nested if
        $situationCode = ($memberAIsInATeam ? "A" : "!A") . "_" . ($memberBIsInATeam ? "B" : "!B");
        switch ($situationCode) {
            // If one user is already in a team, put the second user in the same team.
            case "A_!B":
                $memberATeam = $this->getMemberTeam($link->getMemberA());
                if (!$memberATeam->isFull()) {
                    $memberATeam->addMember($link->getMemberB());
                    $this->retrySkippedLinks();
                }
                break;

            // If one user is already in a team, put the second user in the same team.
            // In this case, it's the same case as in A_!B but inversed
            case "!A_B":
                $link->reverse();
                $this->digestLink($link);
                break;

            // If the two are in a team ignore the link
            // It mean that they are already together or a better preference put them in separate team
            case "A_B":
                break;

            // None of the user are in a team, so we put them together in an empty team if possible
            // If no empty team is possible, we don't try to put them in a partially filled team
            // -> Doing that, we leave room for other preference to fill a team
            case "!A_!B":
                $emptyTeam = current(array_filter($this->teams, static function (Team $team) {
                    return $team->isEmpty();
                }));

                // None are in a team and a team is empty, put them in the team
                if ($emptyTeam) {
                    $emptyTeam->addMember($link->getMemberA());
                    $emptyTeam->addMember($link->getMemberB());
                    $this->retrySkippedLinks();

                    return;
                } else {
                    // None are in a team and no team is empty
                    //     skip it for now, we will retry them when possible
                    //     try the next one then come back as soon as one of the user is added, you'll be back to the first case
                    $this->skippedLink[] = $link;
                }
                break;
        }

    }

    /**
     * Retry skipped links
     *
     * This function is called once a team configuration changed, which mean that maybe we can now process some of the previously skipped links
     *
     * @return void
     */
    public function retrySkippedLinks(): void
    {
        $links              = $this->skippedLinks;
        $this->skippedLinks = [];
        foreach ($links as $link) {
            $this->digestLink($link);
        }
    }

    /**
     * Ensure member as team
     *
     * @param string $member
     *
     * @return void
     */
    public function ensureMemberAsTeam(string $member): void
    {
        if (!$this->memberIsInATeam($member)) {
            /** @var Team[] $freeTeams */
            $freeTeams = array_filter($this->teams, static function (Team $team) {
                return !$team->isFull();
            });
            shuffle($freeTeams);
            $chosenTeam = current($freeTeams);

            $chosenTeam->addMember($member);
            $this->retrySkippedLinks();
        }
    }

    /**
     * Member is in ateam
     *
     * @param string $member
     *
     * @return bool
     */
    public function memberIsInATeam(string $member): bool
    {
        return $this->getMemberTeam($member) != null;
    }

    /**
     * Get member team
     *
     * @param string $member
     *
     * @return Team|null
     */
    public function getMemberTeam(string $member): ?Team
    {
        foreach ($this->teams as $team) {
            if ($team->containMember($member)) {
                return $team;
            }
        }

        return null;
    }

    /**
     * Output result
     *
     * @return void
     */
    public function outputResult(): void
    {
        foreach ($this->teams as $team) {
            printf("== %s ==\n",
                $team->getName()
            );
            foreach ($team->getMembers() as $member) {
                printf("- %s\n",
                    $member
                );
            }
            printf("\n");
        }
    }
}
