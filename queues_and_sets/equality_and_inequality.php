<?php
/**
 * @author pwr013
 *
 * Write a program for checking the possibility of assigning values to variables.
 * Reference https://stepik.org/course/1547 , task 2.3.4.
 *
 * Solution is based on disjoint set.
 */
namespace pwr;

/**
 * Simply disjoint set
 * @package pwr
 */
class DisjointSet
{
    /**
     * Set values.
     * @var array
     */
    protected $set;

    /**
     * Additional array for rank.
     * @var array
     */
    protected $rank;

    /**
     * Count of variables.
     * @var int
     */
    protected $variables_count;

    /**
     * DisjointSet constructor.
     * @param int $variables_count defines variables count.
     */
    public function __construct($variables_count)
    {
        $this->set = [];
        $this->rank = [];

        for ($i = 1; $i <= $variables_count; $i++) {
            $this->makeSet(["ID" => $i]);
        }
    }

    /**
     * Add value to set.
     * @param array $value ["ID" => int].
     */
    public function makeSet($value)
    {
        $this->set[$value["ID"]] = $value;
        $this->rank[$value["ID"]] = 0;
    }

    /**
     * Find value by index.
     * Implements path compression optimization.
     * @param int $index
     * @return int
     */
    public function find($index)
    {
        // Simply search in set
        /*
        while($index != $this->set[$index]["ID"]) {
            $index = $this->set[$index]["ID"];
        }
        return $index;
        */

        // Optimization: recursive ID reassign to exclude "empty" set members and directly connect "child" to "parent".
        if ($index != $this->set[$index]["ID"])
            $this->set[$index]["ID"] = $this->find($this->set[$index]["ID"]);
        return $this->set[$index]["ID"];
    }

    /**
     * Create union of two set values.
     * Implements rank optimization.
     * @param int $index_A
     * @param int $index_B
     */
    public function union($index_A, $index_B)
    {
        $indexA = $this->find($index_A);
        $indexB = $this->find($index_B);

        if ($indexA === $indexB)
            return;

        // Rank defines which "tree" is greater, so the smaller is attached to a larger.
        if ($this->rank[$indexA] > $this->rank[$indexB]) {
            // Now B are "empty" and points to A
            $this->set[$indexB]["ID"] = $indexA;
        } else {
            // Now A are "empty" and points to B
            $this->set[$indexA]["ID"] = $indexB;

            if ($this->rank[$indexA] == $this->rank[$indexB])
                $this->rank[$indexB]++;
        }
    }

    /**
     * Force path compression optimization.
     */
    public function squeeze()
    {
        for ($i = 1; $i <= $this->variables_count; $i++)
            $this->find($i);
    }

    /**
     * Check if there are two values of the same set.
     * @param int $index_A
     * @param int $index_B
     * @return bool true if the same, false otherwise
     */
    public function isIntersect($index_A, $index_B)
    {
        return ($this->find($index_A) === $this->find($index_B)) ? true : false;
    }
}

/*
// For Stepik test environment
$variables_count = $equal = $inequal = 0;
fscanf(STDIN, "%d %d %d".PHP_EOL, $variables_count, $equal, $inequal);

$ds = new DisjointSet($variables_count);

for ($i = 0; $i < $equal; $i++) {
    fscanf(STDIN, "%d %d".PHP_EOL, $indexA, $indexB);
    $ds->union($indexA, $indexB);
}

$ds->squeeze();

$result = 1;
for ($i = 0; $i < $inequal; $i++) {
    fscanf(STDIN, "%d %d".PHP_EOL, $indexA, $indexB);
    if ($ds->isIntersect($indexA, $indexB)) {
        $result = 0;
        break;
    }
}

echo $result;
*/

$ds = new DisjointSet(6);
$ds->union(2, 3);
$ds->union(1, 5);
$ds->union(2, 5);
$ds->union(3, 4);
$ds->union(4, 2);

$ds->squeeze();
$result = true;

$result &= !$ds->isIntersect(6, 1);
echo nl2br((int)$result.PHP_EOL);

$result &= !$ds->isIntersect(4, 6);
echo nl2br((int)$result.PHP_EOL);

$result &= !$ds->isIntersect(4, 5);
echo nl2br((int)$result.PHP_EOL);
