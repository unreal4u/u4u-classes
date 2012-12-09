<?php

include('../rutverifier.class.php');

$rutVerifier = new rutverifier();

var_dump($rutVerifier->isValidRUT('30.686.957-4'));
var_dump($rutVerifier->isValidRUT('30686957-4'));
var_dump($rutVerifier->isValidRUT('306869574'));

var_dump($rutVerifier->isValidRUT('30.686.957-0'));

var_dump($rutVerifier->formatRUT(false));
