<?php

require_once __DIR__ . '/vendor/autoload.php';

if( ($_SERVER["REQUEST_URI"] == '/')
    || ( stristr($_SERVER["REQUEST_URI"], 'CPTAC-'))
    || ( stristr($_SERVER["REQUEST_URI"], 'available_assays'))) {
    include_once($_SERVER["PATH_TO_CORE"] . "slim_framework/SWPG/autoload-local.php");
}
