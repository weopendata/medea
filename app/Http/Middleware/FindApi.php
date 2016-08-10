<?php

namespace App\Http\Middleware;

use Closure;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class FindApi
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

        $personalFindsOnly = !empty($input['myfinds']) && $input['myfinds'] == "true";

        $embargoEnabled = !empty($request->input('embargo')) && $request->input('embargo') == "true";

        // Default validation status is "gevalideerd", this is also
        // applied in the API controller
        $findStatus = $request->input('status', 'gevalideerd');

        $this->validateFindRequest($personalFindsOnly, $findStatus, $embargoEnabled, $user);

        return $next($request);
    }

    private function userHasRoleIn($userRoles, $comparingRoles)
    {
        return count(array_intersect($userRoles, $comparingRoles)) > 0;
    }

    /**
     * Validate a find, aborts when a rule is crossed
     * @param  boolean $personalFindsOnly
     * @param  string $status
     * @param  boolean $embargo
     *
     * @return void
     */
    public function validateFindRequest($personalFindsOnly, $status, $embargo, $user)
    {
        // An empty user cannot select myfinds
        if (empty($user) && $personalFindsOnly) {
            abort('401');
        }

        if (empty($user) && $status != 'gevalideerd') {
            abort('401', 'U bent terechtgekomen op een pagina die niet beschikbaar is voor u.');
        }

        // Up until here we're sure the user is either logged in
        // or has only selected validated finds

        if (!empty($user)) {
            $userRoles = $user->getRoles();

            // Don't show unvalidated finds to users who are not allowed to
            if (!$this->userHasRoleIn($userRoles, $this->getRolesForUnvalidated())
                && $status != 'gevalideerd'
                && !$personalFindsOnly) {
                abort('403');
            }

            if (!$this->userHasRoleIn($userRoles, $this->getRolesForEmbargo())
                && $embargo
                && !$personalFindsOnly
            ) {
                abort('403');
            }
        }
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
