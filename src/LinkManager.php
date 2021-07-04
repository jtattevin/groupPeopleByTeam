<?php


namespace App;


use App\Model\Link;

/**
 * Class LinkManager
 *
 * @package App
 */
class LinkManager
{

    /**
     * @var Link[]
     */
    private $links = [];

    /**
     * Link
     *
     * @param $memberA
     * @param $memberB
     * @param $score
     *
     * @return void
     */
    public function link($memberA, $memberB, $score): void
    {
        $foundLink = null;

        // We search if a link match the current combinaison
        foreach ($this->links as $link) {
            if ($link->match($memberA, $memberB)) {
                $foundLink = $link;
                break;
            }
        }

        // This link doesn't exist yet, we create a new one
        if (!$foundLink) {
            $foundLink     = new Link($memberA, $memberB);
            $this->links[] = $foundLink;
        }

        $foundLink->addWeight($score);
    }

    /**
     * Get ordered link with positive weight
     *
     * @return Link[]
     */
    public function getOrderedLinkWithPositiveWeight(): array
    {
        // If it's stricly below 0, that mean that both the user didn't make a preferences
        // If it's equal to 0, it mean that it was not a choice + the last choice, so still a preference
        $filteredLinks = array_filter($this->links, static function (Link $link) {
            return $link->getWeight() >= 0;
        });
        usort($filteredLinks, static function (Link $linkA, Link $linkB) {
            return $linkB->getWeight() - $linkA->getWeight();
        });

        return $filteredLinks;
    }

    /**
     * Output plant uml
     *
     * @param string $filename
     * @param bool   $onlyPositive
     *
     * @return void
     */
    public function outputPlantUml(string $filename, bool $onlyPositive): void
    {
        $file = fopen($filename, "wb");

        $members = [];
        foreach ($this->links as $link) {
            $members[] = $link->getMemberA();
            $members[] = $link->getMemberB();
        }

        fprintf($file, "@startuml\n");
        foreach (array_unique($members) as $member) {
            fprintf($file, "class %s\n",
                $member
            );
        }
        foreach ($this->links as $link) {
            if (!$onlyPositive || $link->getWeight() > 0) {
                fprintf($file, "%1\$s <-[thickness=%4\$d]-> %2\$s : %3\$d\n",
                    $link->getMemberA(),
                    $link->getMemberB(),
                    $link->getWeight(),
                    max($link->getWeight() + 1, 1),
                );
            }
        }
        fprintf($file, "@enduml\n");
    }
}
