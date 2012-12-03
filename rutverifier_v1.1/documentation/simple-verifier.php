<?php

include('../rutverifier.class.php');

$rutVerifier = new rutverifier();

var_dump($rutVerifier->isValidRUT('14609732-4'));
var_dump($rutVerifier->isValidRUT('14.609.732-4'));
var_dump($rutVerifier->isValidRUT('146097324'));
var_dump($rutVerifier->isValidRUT('14609732-2'));
