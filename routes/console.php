<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('chores:notify')->everyMinute();
Schedule::command('chores:reconcile')->dailyAt('00:00');
