<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Person;

class Roles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $roles
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $roles)
    {
        // A user needs to be logged in
        if (empty($request->user())) {
            return redirect()->guest('/login');
        }

        // Get the allowed roles for the route
        $allowedRoles = explode('|', $roles);

        // Get the roles of the logged in user
        $person = $request->user();

        $userRoles = $person->getRoles();

        if (empty(array_intersect($userRoles, $allowedRoles))
            && !in_array('administrator', $userRoles)
        ) {
            abort('403');
        }

        return $next($request);
    }
}
