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
     * Gets the previous, next and current value of array. Current equals given id if id is found, otherwise it will
     * return false
     *
     * Possible use-cases:
     * 1- Show next and previous search result given that you have a searchresult in the form of an array with id's
     * 2- Every operation where you need to be aware if there is a possible next and/or previous id
     *
     * @param mixed $id Any value of the array
     * @param array $valuesArray The array with all the possible values
     * @return array Returns an array with indexes 'prev', 'next' and 'curr'
     */
    public function getNextAndPrevious($id, $valuesArray) {
        $previous = $next = $returnId = false;
        if (is_array($valuesArray) && in_array($id, $valuesArray)) {
            $arrayObject = new \ArrayObject($valuesArray);
            $arrayIterator = $arrayObject->getIterator();
            while ($arrayIterator->valid() && empty($returnId)) {
                if ($arrayIterator->current() === $id) {
                    $returnId = $id;
                } else {
                    $previous = $arrayIterator->current();
                }
                $arrayIterator->next();
            }

            if ($arrayIterator->valid()) {
                $next = $arrayIterator->current();
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