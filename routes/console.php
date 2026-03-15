<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('chores:notify')->everyMinute();
