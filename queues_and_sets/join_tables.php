<?php
/**
 * @author pwr013
 *
 * Write a program to simulate table joins.
 * Reference https://stepik.org/course/1547 , task 2.3.3.
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
     * Current maximum size from all "tables".
     * @var int
     */
    protected $table_max;

    /**
     * DisjointSet constructor.
     * @param int $tables_count defines tables count.
     * @param array $table_sizes defines initial table sizes.
     */
    public function __construct($tables_count, $table_sizes)
    {
        $this->set = [];
        $this->rank = [];
        $this->table_max = 0;

        for ($i = 0; $i < $tables_count; $i++) {
            // numbering from 1 - see task conditions
            $table = ["ID" => $i + 1, "ROWS" => $table_sizes[$i]];
            $this->makeSet($table);

            $this->table_max = max($this->table_max, $table_sizes[$i]);
        }
    }

    /**
     * Add value to set.
     * @param array $value ["ID" => int, "ROWS" => int].
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
            $this->set[$indexA]["ROWS"] += $this->set[$indexB]["ROWS"];

            // Now B are "empty" and points to A
            $this->set[$indexB]["ID"] = $indexA;
            $this->set[$indexB]["ROWS"] = 0;

            $this->table_max = max($this->table_max, $this->set[$indexA]["ROWS"]);
        } else {
            $this->set[$indexB]["ROWS"] += $this->set[$indexA]["ROWS"];

            // Now A are "empty" and points to B
            $this->set[$indexA]["ID"] = $indexB;
            $this->set[$indexA]["ROWS"] = 0;

            $this->table_max = max($this->table_max, $this->set[$indexB]["ROWS"]);

            if ($this->rank[$indexA] == $this->rank[$indexB])
                $this->rank[$indexB]++;
        }
    }

    /**
     * Get current max table size.
     * @return int
     */
    public function getMax()
    {
        return $this->table_max;
    }
}

/*
// For Stepik test environment
$table_sizes = [];
$tables_count = $unions_count = 0;

fscanf(STDIN, "%d %d".PHP_EOL, $tables_count, $unions_count);
$mask = rtrim(str_repeat("%d ", $tables_count));
$table_sizes = fscanf(STDIN, $mask.PHP_EOL);

$ds = new DisjointSet($tables_count, $table_sizes);

$table_max_size  = [];
$indexA = $indexB = 1;
for ($i = 0; $i < $unions_count; $i++) {
    fscanf(STDIN, "%d %d".PHP_EOL, $indexA, $indexB);
    $ds->union($indexA, $indexB);
    $table_max_size[] = $ds->getMax();
}

echo implode(PHP_EOL, $table_max_size);
*/

$ds = new DisjointSet(5, [1, 1, 1, 1, 1]);

$ds->union(3, 5);
echo nl2br($ds->getMax().PHP_EOL);

$ds->union(2, 4);
echo nl2br($ds->getMax().PHP_EOL);

$ds->union(1, 4);
echo nl2br($ds->getMax().PHP_EOL);

$ds->union(5, 4);
echo nl2br($ds->getMax().PHP_EOL);

$ds->union(5, 3);
echo nl2br($ds->getMax().PHP_EOL);
