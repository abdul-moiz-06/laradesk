<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Sign-in link lifetime
    |--------------------------------------------------------------------------
    |
    | How many minutes a passwordless magic sign-in link stays valid. Kept
    | short: the link is single-use and expiring, a one-time key, not a
    | standing credential.
    |
    */

    'login_link_minutes' => (int) env('PORTAL_LOGIN_LINK_MINUTES', 15),

];
