<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Ledger append-only
    |--------------------------------------------------------------------------
    |
    | 'chain' mengaktifkan hash chain (prev_hash/hash). Bila dimatikan,
    | integritas masih dijaga trigger immutable di database.
    |
    */

    'enabled' => true,

    'chain' => (bool) env('LEDGER_CHAIN', true),

    'lock_key' => 'ledger-chain',

    'verify_schedule' => '0 1 * * *',

];
