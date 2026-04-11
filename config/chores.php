<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Carryover Days
    |--------------------------------------------------------------------------
    |
    | The number of days a missed chore will continue to appear on a child's
    | dashboard. After this period, the miss is still recorded but no longer
    | shown as an outstanding task.
    |
    */

    'carryover_days' => env('CHORES_CARRYOVER_DAYS', 7),

];
