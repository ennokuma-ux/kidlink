<?php

return [
    /*
    |--------------------------------------------------------------------------
    | View Cache
    |--------------------------------------------------------------------------
    |
    | When this option is set to true, the compiled views will be cached
    | and the application will not have to recompile the views on each
    | request. This can significantly improve the performance of your
    | application.
    |
    */

    'cache' => env('VIEW_CACHE', false),
];