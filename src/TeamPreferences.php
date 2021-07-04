<?php

namespace App;

use Symfony\Component\Yaml\Yaml;

/**
 * Class TeamPreferences
 *
 * @package App
 */
class TeamPreferences
{

    /**
     * @var string
     */
    private $yamlPath;

    /**
     * @var array
     */
    private $yaml;

    /**
     * TeamPreferences constructor.
     *
     * @param string $rootFolder
     */
    public function __construct(string $rootFolder)
    {
        $this->yamlPath = $rootFolder . "/teamPreferences.yaml";
    }

    /**
     * Load
     *
     * @return void
     */
    public function load(): void
    {
        $this->yaml = Yaml::parseFile($this->yamlPath);
    }

    /**
     * Generate random setting
     *
     * @param int $memberAmount
     * @param int $teamSize
     *
     * @return void
     */
    public function generateRandomSetting(int $memberAmount, int $teamSize): void
    {
        // Generate N members
        $members = array_map(static function (int $memberId) {
            return "user_" . $memberId;
        }, range(1, $memberAmount));


        $membersChoice = [];
        foreach ($members as $currentMember) {
            // Each member can not choose to be with them, so we remove the current user from the list
            $possibleMembers = array_filter($members, static function (string $possibleMember) use ($currentMember) {
                return $currentMember != $possibleMember;
            });

            shuffle($possibleMembers);
            $membersChoice[$currentMember] = array_slice($possibleMembers, 0, $teamSize - 1);
        }

        file_put_contents(
            $this->yamlPath,
            Yaml::dump($membersChoice)
        );
        $this->load();
    }

    /**
     * Get members
     *
     * @return array
     */
    public function getMembers(): array
    {
        return array_keys($this->yaml);
    }

    /**
     * Get preferences
     *
     * @param string $member
     *
     * @return array|mixed
     */
    public function getPreferences(string $member): array
    {
        return $this->yaml[$member] ?: [];
    }

    /**
     * Output plant uml
     *
     * @param string $filename
     *
     * @return void
     */
    public function outputPlantUml(string $filename): void
    {
        $file = fopen($filename, "wb");

        fprintf($file, "@startuml\n");
        foreach ($this->getMembers() as $member) {
            fprintf($file, "class %s\n",
                $member
            );
        }
        foreach ($this->getMembers() as $member) {
            foreach ($this->getPreferences($member) as $preferencePos => $otherMember) {
                fprintf($file, "%1\$s -[thickness=%3\$d]-> %2\$s : %3\$d\n",
                    $member,
                    $otherMember,
                    $preferencePos,
                );
            }
        }
        fprintf($file, "@enduml\n");
    }
}
