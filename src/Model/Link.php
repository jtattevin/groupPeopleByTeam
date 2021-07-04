<?php


namespace App\Model;

/**
 * Class Link
 *
 * @package App\Model
 */
class Link
{

    /**
     * @var string
     */
    private $memberA;

    /**
     * @var string
     */
    private $memberB;

    private $weight = 0;

    /**
     * Link constructor.
     *
     * @param string $memberA
     * @param string $memberB
     */
    public function __construct(string $memberA, string $memberB)
    {
        $this->memberA = $memberA;
        $this->memberB = $memberB;
        $this->weight  = 0;
    }

    /**
     * Add weight
     *
     * @param int $weight
     *
     * @return void
     */
    public function addWeight(int $weight): void
    {
        $this->weight += $weight;
    }

    /**
     * Match
     *
     * @param string $memberA
     * @param string $memberB
     *
     * @return bool
     */
    public function match(string $memberA, string $memberB):bool
    {
        return
            ($memberA == $this->memberA && $memberB == $this->memberB)
            ||
            ($memberB == $this->memberA && $memberA == $this->memberB);

    }

    /**
     * Get member a
     *
     * @return string
     */
    public function getMemberA(): string
    {
        return $this->memberA;
    }

    /**
     * Get member b
     *
     * @return string
     */
    public function getMemberB(): string
    {
        return $this->memberB;
    }

    /**
     * Get weight
     *
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * Reverse the link
     *
     * @return void
     */
    public function reverse(): void
    {
        [$this->memberA, $this->memberB] = [$this->memberB, $this->memberA];
    }
}
