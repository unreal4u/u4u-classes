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
     *
     * @return float Returns the exact time+microtime
     */
    public function getExactTime() {
        return microtime(true);
    }

    /**
     * Gets the current used memory footprint
     *
     * @param string $format Choose between "B" (bytes), "KB", "KiB", "MB", "MiB", "GB", "GiB". Defaults to "B"
     * @return int Returns the memory usage in the requested format
     */
    public function getMemoryUsage($format='B') {
        return $this->formatNumber(memory_get_usage(), $format);
    }

    /**
     * Gets the peak memory usage
     *
     * @param string $format Choose between "B" (bytes), "KB", "KiB", "MB", "MiB", "GB", "GiB". Defaults to "B"
     * @return int Returns the peak memory usage in the requested format
     */
    public function getPeakMemoryUsage($format='B') {
        return $this->formatNumber(memory_get_peak_usage(), $format);
    }

    /**
     * Formats a number according to the given format
     *
     * @param float $number Any number
     * @param string $format Choose between "B" (bytes), "KB", "KiB", "MB", "MiB", "GB", "GiB". Defaults to "B"
     * @return int Returns the value in the requested format
     */
    private function formatNumber($number, $format='B') {
        $multiplier = 1;
        switch($format) {
            case 'KB':
                $multiplier = 1000;
                break;
            case 'KiB':
                $multiplier = 1024;
                break;
            case 'MB':
                $multiplier = 1000 * 1000;
                break;
            case 'MiB':
                $multiplier = 1024 * 1024;
                break;
            case 'GB':
                $multiplier = 1000 * 1000 * 1000;
                break;
            case 'GiB':
                $multiplier = 1024 * 1024 * 1024;
                break;
        }

        return round($number / $multiplier);
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
            $this->data[$identifier]['startMemorySize'] = $this->getMemoryUsage();
            $this->data[$identifier]['startMemoryPeakSize'] = $this->getPeakMemoryUsage();
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

        if (!empty($this->data[$identifier]['endTime'])) {
            $totalTime = $this->data[$identifier]['endTime'] - $this->data[$identifier]['startTime'];
        } else if (array_key_exists($identifier, $this->data)) {
            $this->data[$identifier]['endTime'] = $time;
            $this->data[$identifier]['endMemorySize'] = $this->getMemoryUsage();
            $this->data[$identifier]['endMemoryPeakSize'] = $this->getPeakMemoryUsage();
        }

        return $this->getDiff($identifier);
    }

    /**
     * Delivers the memory difference of a identifier
     *
     * @param string $identifier The identifier of the data we want to return
     * @param string $type Can be "time", "memory" or "peakmemory". Defaults to "time"
     */
    public function getDiff($identifier, $type='time') {
        $return = false;

        if (!empty($identifier) AND !empty($this->data[$identifier]['endMemorySize'])) {
            switch($type) {
                case 'time':
                    $return = sprintf('%.'.$this->decimals.'f', $this->data[$identifier]['endTime'] - $this->data[$identifier]['startTime']);
                    break;
                case 'memory':
                    $return = $this->data[$identifier]['endMemorySize'] - $this->data[$identifier]['startMemorySize'];
                break;
                case 'peakmemory':
                    $return = $this->data[$identifier]['endMemoryPeakSize'] - $this->data[$identifier]['startMemoryPeakSize'];
                break;
            }
        }

        return $return;
    }
}
