<?php
/**
 * @author pwr013
 *
 * Write a program to simulate parallel processing of tasks.
 * Reference https://stepik.org/course/1547 , task 2.3.2.
 *
 * Solution is based on а MIN binary heap.
 * All basic methods are implemented, although not all of them are required to solve the current task.
 */
namespace pwr;

/**
 * Declaration of the core methods for Queue
 * @package pwr
 */
interface Queue
{
    public function insert($value);
    public function top();
    public function extract();
    public function change($index, $value);
    public function remove($index);
}

/**
 * Implementation of the Queue methods
 * @package pwr
 */
class BinaryHeap implements Queue, \Countable, \Iterator
{
    /**
     * Heap data.
     * @var array
     */
    protected $heap;

    /**
     * Heap size.
     * @var int
     */
    protected $size;

    /**
     * Heap type.
     * @var int
     */
    protected $heapType;

    /**
     * Defines min heap.
     */
    const MIN_HEAP = 0;

    /**
     * Defines max heap.
     */
    const MAX_HEAP = 1;

    /**
     * BinaryHeap constructor.
     * @param int $heapType defines MIN heap or MAX heap.
     * @throws \Exception while incorrect type used.
     */
    public function __construct($heapType)
    {
        if ($heapType === self::MIN_HEAP || $heapType === self::MAX_HEAP)
            $this->heapType = $heapType;
        else
            throw new \Exception("Wrong heap type, use BinaryHeap::MIN_HEAP or BinaryHeap::MAX_HEAP instead.");

        $this->size = 0;
        $this->heap = [];
    }

    /**
     * Swap two nodes.
     * @param int $indexA
     * @param int $indexB
     */
    protected function swap($indexA, $indexB)
    {
        if ($indexA === $indexB)
            return;
        $tmp = $this->heap[$indexA];
        $this->heap[$indexA] = $this->heap[$indexB];
        $this->heap[$indexB] = $tmp;
    }

    /**
     * Compare two node values.
     * @param mixed $valueA
     * @param mixed $valueB
     * @return int 1 if $valueA > $valueB, 0 if they are ===, -1 if $valueA < $valueB
     */
    protected function compare($valueA, $valueB)
    {
        if ($valueA === $valueB)
            return 0;
        return ($valueA > $valueB ? 1 : -1);
    }

    /**
     * Sifts the node DOWN until the correct heap structure is restored.
     * @param int $index
     */
    protected function siftDown($index)
    {
        while (2 * $index + 1 < $this->size) {
            $left = 2 * $index + 1;
            $right = 2 * $index + 2;
            $selected = $left;

            if ($this->heapType === self::MIN_HEAP) {
                if ($right < $this->size && $this->compare($this->heap[$right], $this->heap[$left]) < 0) {
                    $selected = $right;
                }
                if ($this->compare($this->heap[$index], $this->heap[$selected]) <= 0)
                    break;
            }
            elseif ($this->heapType === self::MAX_HEAP) {
                if ($right < $this->size && $this->compare($this->heap[$right], $this->heap[$left]) > 0) {
                    $selected = $right;
                }
                if ($this->compare($this->heap[$index], $this->heap[$selected]) >= 0)
                    break;
            }

            $this->swap($index, $selected);
            $index = $selected;
        }
    }

    /**
     * Sifts the node UP until the correct heap structure is restored.
     * @param int $index
     */
    protected function siftUp($index)
    {
        while (true) {
            $parent = (int)floor(($index - 1)/2);
            if ($this->heapType === self::MIN_HEAP) {
                if (!($this->compare($this->heap[$index], $this->heap[$parent]) < 0 && $index > 0))
                    break;
            }
            elseif ($this->heapType === self::MAX_HEAP) {
                if (!($this->compare($this->heap[$index], $this->heap[$parent]) > 0 && $index > 0))
                    break;
            }
            $this->swap($index, $parent);
            $index = $parent;
        }
    }

    /**
     * Return value of the node on the top.
     * @return mixed|null value
     */
    public function top()
    {
        return ($this->size > 0 ? $this->heap[0] : null);
    }

    /**
     * Extracts the node from top of the heap.
     * @return mixed|null value
     */
    public function extract()
    {
        if ($this->size < 1)
            return null;

        $top = $this->heap[0];
        if ($this->size-- > 1) {
            $this->heap[0] = array_pop($this->heap);
            $this->siftDown(0);
        } else {
            $this->heap = [];
            $this->size = 0;
        }
        return $top;
    }

    /**
     * Add node to heap.
     * @param mixed $value node value
     */
    public function insert($value)
    {
        $this->heap[] = $value;
        $this->siftUp(++$this->size - 1);
    }

    /**
     * Change node value and restore heap structure after this.
     * @param int $index
     * @param mixed $value
     */
    public function change($index, $value)
    {
        $old = $this->heap[$index];
        $compare = $this->compare($value, $old);

        if ($compare === 0)
            return;

        $this->heap[$index] = $value;

        if ($this->heapType === self::MIN_HEAP)
            $compare > 0 ? $this->siftDown($index) : $this->siftUp($index);
        elseif ($this->heapType === self::MAX_HEAP)
            $compare > 0 ? $this->siftUp($index) : $this->siftDown($index);
    }

    /**
     * Removes node from heap and restore heap structure after this.
     * @param int $index
     */
    public function remove($index)
    {
        if ($this->heapType === self::MIN_HEAP)
            $this->heap[$index] = -INF;
        elseif ($this->heapType === self::MAX_HEAP)
            $this->heap[$index] = INF;

        $this->siftUp($index);
        $this->extract();
    }

    /**
     * Build heap using array as node values.
     * @param mixed $array
     */
    public function buildHeap($array)
    {
        $this->heap = $array;
        $this->size = count($array);

        for ($i = (int)floor($this->size/2); $i >= 0; $i--) {
            $this->siftDown($i);
        }
    }

    /**
     * Sort heap "in place".
     */
    public function sortHeap()
    {
        $size = $this->size;

        for ($i = 0; $i < $size-1; $i++) {
            $this->swap(0, $this->size-1);
            $this->size--;
            $this->siftDown(0);
        }
        $this->size = $size;
    }

    /**
     * Return elements count. Implementation of \Countable interface.
     * @return int
     */
    public function count()
    {
        return $this->size;
    }

    /**
     * Return current element. Implementation of \Iterator interface.
     * @return mixed
     */
    public function current()
    {
        return current($this->heap);
    }

    /**
     * Return key of the current element. Implementation of \Iterator interface.
     * @return mixed
     */
    public function key()
    {
        return key($this->heap);
    }

    /**
     * Advance to next element. Implementation of \Iterator interface.
     * @return mixed
     */
    public function next()
    {
        return next($this->heap);
    }

    /**
     * Rewind to first element. Implementation of \Iterator interface.
     * @return mixed
     */
    public function rewind()
    {
        return reset($this->heap);
    }

    /**
     * Validity check of element. Implementation of \Iterator interface.
     * @return bool
     */
    public function valid()
    {
        return !is_null(key($this->heap));
    }
}

/**
 * Just slightly changed the comparison logic
 * @package pwr
 */
class ParallelProcessing extends BinaryHeap
{
    /**
     * Compare two node values.
     * @param mixed $valueA array [$time, $core]
     * @param mixed $valueB array [$time, $core]
     * @return int 1 if $valueA > $valueB, 0 if they are ===, -1 if $valueA < $valueB
     */
    protected function compare($valueA, $valueB)
    {
        // $value: [$time, $core]
        $time_compare = parent::compare($valueA[0], $valueB[0]);
        $core_compare = parent::compare($valueA[1], $valueB[1]);

        if ($time_compare === 0 && $core_compare === 0)
            return 0;

        if ($time_compare !== 0)
            return $time_compare;
        else
            return $core_compare;
    }
}

try {
    $heap = new ParallelProcessing(BinaryHeap::MIN_HEAP);

    /*
    // For Stepik test environment
    fscanf(STDIN, "%d %d".PHP_EOL, $core, $tasks);
    $mask = rtrim(str_repeat("%d ", $tasks));
    $timeline = fscanf(STDIN, $mask.PHP_EOL);
    */

    $core = 4;
    $tasks = 20;
    $timeline = [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1];

    $log = [];

    // Init cores
    for ($i = 0; $i < $core; $i++) {
        $heap->insert([0, $i]);
    }

    // Let's process something
    for ($i = 0; $i < $tasks; $i++) {
        $task = $heap->extract();
        $time = $task[0];
        $core = $task[1];

        $heap->insert([$timeline[$i] + $time, $core]);
        $log[] = $core." ".$time;
    }

    // echo implode(PHP_EOL, $log);
    echo nl2br(implode(PHP_EOL, $log));
}
catch (\Exception $e) {
    echo nl2br($e->getMessage().PHP_EOL.PHP_EOL);
}