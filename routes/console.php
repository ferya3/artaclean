<?php

declare(strict_types=1);

use App\Enums\QuoteStatus;
use App\Models\Quote;
use Illuminate\Support\Facades\Schedule;

/*
|------------------------------------------------------------------------------
| Scheduled work
|------------------------------------------------------------------------------
*/

/**
 * A quote is priced against the exchange rate of the day it was sent. Once it
 * passes its validity date it has to stop reading as a live offer, both in the
 * pipeline reports and in the dealer panel.
 */
Schedule::call(function () {
    Quote::query()
        ->whereIn('status', [QuoteStatus::Sent->value, QuoteStatus::Pending->value, QuoteStatus::InReview->value])
        ->whereNotNull('valid_until')
        ->whereDate('valid_until', '<', now())
        ->update(['status' => QuoteStatus::Expired->value]);
})->dailyAt('02:00')->name('expire-stale-quotes')->onOneServer();

// Horizon's throughput graphs are built from these snapshots.
Schedule::command('horizon:snapshot')->everyFiveMinutes();

Schedule::command('queue:prune-failed --hours=336')->weekly();
Schedule::command('model:prune')->daily();

// Keeps Scout's index in step after bulk edits or an import.
Schedule::command('scout:import "App\Models\Product"')->dailyAt('03:30')->onOneServer();
