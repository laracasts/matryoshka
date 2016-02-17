<?php

namespace Laracasts\Dolly;

use Cache;

class FlushViews
{
    /**
     * Handle the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     */
    public function handle($request, $next)
    {
        if (app()->environment() === 'local') {
            Cache::tags('views')->flush();
        }

        return $next($request); 
    }
}
