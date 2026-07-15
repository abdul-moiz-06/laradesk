<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Impersonation time box
    |--------------------------------------------------------------------------
    |
    | The maximum number of minutes a SuperAdmin may stay signed in as a
    | company user before the session is automatically ended and they are
    | returned to the platform panel. Kept short: impersonation is a
    | break-glass support tool, not a place to live.
    |
    */

    'max_minutes' => (int) env('IMPERSONATION_MAX_MINUTES', 30),

];
