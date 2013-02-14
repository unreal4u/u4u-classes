<?php

include('../debugInfo.class.php');

throw_exceptions();

function a() {
    trigger_error("If you type google... into google... ", E_USER_ERROR);
}

function b() {
    throw new Exception('You can break the internet!');
}

try {
    a();
} catch (Exception $e) {
    debug('Captured exception 1: "'.$e->getMessage().'"');

    try {
        b();
    } catch (Exception $e) {
        debug('Captured exception 2: "'.$e->getMessage().'"');
    }
}
