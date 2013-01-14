<?php

/**
 * This class will save data regarding times and memory sizes
 *
 * This class was made because I needed a simple way to benchmark two times, but those times could stack each other up,
 * so this class was born. It isn't fancy or really special, it just stores memory/time and then calculates the
 * difference between each other.
 *
 * @package Benchmark
 * @version 0.1
 * @author Camilo Sperberg - http://unreal4u.com/
 * @license BSD License. Feel free to use and modify
 */
class benchmark {

    /**
     * Private array with all the recorded data
     * @var array
     */
    private $data = array();

    /**
     * With how many decimals we want to print
     * @var int
     */
    public $decimals = 6;

    /**
     * Constructor, can also be used to immediatly record a time
     *
     * @param string $identifier
     */
    public function __construct($identifier='') {
        if (!empty($identifier)) {
            $this->beginCounter($identifier);
        }
    }

    /**
     * Returns the exact time
     */
    private function getExactTime() {
        return microtime(true);
    }

    /**
     * Starts a counter
     *
     * @param string $identifier The identifier of the data we want to return
     * @return boolean Returns always true
     */
    public function beginCounter($identifier='') {
        // First step: get the current exact time
        $time = $this->getExactTime();
        if (!empty($identifier)) {
            $this->data[$identifier]['startTime'] = $time;
            $this->data[$identifier]['startMemorySize'] = memory_get_usage();
            $this->data[$identifier]['startMemoryPeakSize'] = memory_get_peak_usage();
        }

        return true;
    }

    /**
     * Ends a counter and returns the elapsed time between start and end
     *
     * @param string $identifier The identifier of the data we want to return
     * @return float Returns a float containing the difference between start and end time
     */
    public function endCounter($identifier) {
        // First step: get the current exact time
        $time = $this->getExactTime();
        $totalTime = 0;

        if (!empty($this->data[$identifier]['endTime'])) {
            $totalTime = $this->data[$identifier]['endTime'] - $this->data[$identifier]['startTime'];
        } else if (array_key_exists($identifier, $this->data)) {
            $this->data[$identifier]['endTime'] = $time;
            $this->data[$identifier]['endMemorySize'] = memory_get_usage();
            $this->data[$identifier]['endMemoryPeakSize'] = memory_get_peak_usage();
            $totalTime = $this->data[$identifier]['endTime'] - $this->data[$identifier]['startTime'];
        }

        $format = '%.'.$this->decimals.'f';
        return sprintf($format, $totalTime);
    }

    /**
     * Delivers the memory difference of a identifier
     *
     * @param string $identifier The identifier of the data we want to return
     * @param string $type Can be "normal" or "peak"
     */
    public function memoryDiff($identifier, $type='normal') {
        $return = false;

        if (!empty($identifier) AND !empty($this->data[$identifier]['endMemorySize'])) {
            switch($type) {
                case 'normal':
                    $return = $this->data[$identifier]['endMemorySize'] - $this->data[$identifier]['startMemorySize'];
                break;
                case 'peak':
                    $return = $this->data[$identifier]['endMemoryPeakSize'] - $this->data[$identifier]['startMemoryPeakSize'];
                break;
            }
        }

        return $return;
    }
}
