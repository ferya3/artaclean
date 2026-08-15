<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Lead;
use App\Models\Order;
use App\Models\Quote;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies a dealer_id global scope for the duration of the request, so every
 * query in the dealer panel — list, edit, count, widget — is confined to the
 * signed-in dealer without each resource having to remember.
 */
class ScopeToDealer
{
    public function handle(Request $request, Closure $next): Response
    {
        $dealerId = $request->user()?->dealer_id;

        if ($dealerId === null) {
            abort(403);
        }

        foreach ([Lead::class, Quote::class, Order::class] as $model) {
            $model::addGlobalScope(
                'dealer',
                fn (Builder $query) => $query->where($query->getModel()->getTable().'.dealer_id', $dealerId)
            );
        }

        return $next($request);
    }
}
