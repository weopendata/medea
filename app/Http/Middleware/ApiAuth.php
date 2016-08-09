<?php

namespace App\Http\Middleware;

use Closure;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        $input = $request->input();

        $myFindsOnly = !empty($input['myfinds']) && $input['myfinds'] == "true";

        // Default validation status is "gevalideerd", this is also
        // applied in the API controller
        $findStatus = $request->input('status', 'gevalideerd');

        // An empty user cannot select myfinds
        if (empty($user) && $myFindsOnly) {
            abort('401');
        }

        if (empty($user) && $findStatus != 'gevalideerd') {
            abort('401');
        }

        // Up until here we're sure the user is either logged in
        // or has only selected validated finds

        if (!empty($user)) {
            $userRoles = $user->getRoles();

            // Don't show unvalidated finds to users who are not allowed to
            if (!$this->userHasRoleIn($userRoles, $this->getRolesForUnvalidated())
                && $findStatus != 'gevalideerd'
                && !$myFindsOnly) {
                abort('403');
            }

            $embargoEnabled = !empty($request->input('embargo')) && $request->input('embargo') == "true";

            if (!$this->userHasRoleIn($userRoles, $this->getRolesForEmbargo())
                && $embargoEnabled
                && !$myFindsOnly
            ) {
                abort('403');
            }
        }

        return $next($request);
    }

    private function userHasRoleIn($userRoles, $comparingRoles)
    {
        return array_intersect($userRoles, $comparingRoles) > 0;
    }


    /**
     * Get the roles that are allowed to view all
     * unvalidated finds
     *
     * @return array
     */
    private function getRolesForUnvalidated()
    {
        return [
            'validator',
            'registrator',
            'administrator',
            'agency'
        ];
    }

    /**
     * Get the roles that are allowed to view all embargo finds
     *
     * @return array
     */
    private function getRolesForEmbargo()
    {
        return [
            'administrator',
            'researcher',
            'agency'
        ];
    }
}
