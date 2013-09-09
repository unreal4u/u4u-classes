<?php

namespace u4u;

/**
 * General array operations are all encapsulated within this class
 *
 * This class unites all loose array operations I have floating around in my code. The purpose is to gather them all
 * within this single class, so that everything can be properly tested
 *
 * @author unreal4u
 */
class arrayOperations extends \ArrayIterator {

    /**
     * Gets the previous, next and current value of array. Current equals given id
     *
     * @param mixed $id
     * @param array $valuesArray
     * @return array
     */
    public function getNextAndPrevious($id, $valuesArray) {
        $done = $previous = $next = $returnId = false;
        $i = 0;
        if (is_array($valuesArray) && in_array($id, $valuesArray)) {
            reset($valuesArray);
            while ($done !== true || key($valuesArray) !== null) {
                if (current($valuesArray) === $id) {
                    $done = true;
                    if ($i !== 0) {
                        $previous = prev($valuesArray);
                        next($valuesArray);
                    }
                    $next = next($valuesArray);
                    $returnId = $id;
                }
                next($valuesArray);
                $i++;
            }
        }

        return array(
            'prev' => $previous,
            'next' => $next,
            'curr' => $returnId,
        );
    }

    /**
     * Checks whether any of the VALUES of an array are empty
     *
     * Empty values are considered all things that make empty() return true.
     * Please note that this function uses early return when a empty value is found instead of continuing to cycle
     * through all the posibilities.
     *
     * @param array $array
     * @param boolean $recursiveCheck Defaults to false
     * @return boolean Returns false if array has empty values, true otherwise
     */
    public function hasEmptyValues($array, $recursiveCheck=false) {
        $return = false;
        if(is_array($array) && !empty($array)) {
            foreach($array AS $element) {
                if(!empty($recursiveCheck) && is_array($element)) {
                    $return = $this->hasEmptyValues($element, true);
                } else {
                    if (empty($element)) {
                        return true;
                    }
                }
            }
        } else {
            return true;
        }

        return $return;
    }
}