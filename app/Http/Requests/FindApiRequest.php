<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Http\Request as HttpRequest;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class FindApiRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(HttpRequest $request)
    {
        $user = $request->user();

        $input = $request->input();

        $personalFindsOnly = !empty($input['myfinds']) && $input['myfinds'] == "true";

        $embargoEnabled = !empty($request->input('embargo')) && $request->input('embargo') == "true";

        // Default validation status is "gevalideerd", this is also
        // applied in the API controller
        $findStatus = $request->input('status', 'gevalideerd');

        return $this->validateFindRequest($personalFindsOnly, $findStatus, $embargoEnabled, $user);
    }

    protected function userHasRoleIn($userRoles, $comparingRoles)
    {
        return count(array_intersect($userRoles, $comparingRoles)) > 0;
    }

    /**
     * Validate a find, aborts when a rule is not fulfilled
     *
     * @param  boolean $personalFindsOnly
     * @param  string $status
     * @param  boolean $embargo
     *
     * @return boolean
     */
    protected function validateFindRequest($personalFindsOnly, $status, $embargo, $user)
    {
        // An empty user cannot select myfinds
        if (empty($user) && $personalFindsOnly) {
            abort('401', 'U moet zich inloggen om persoonlijke vondsten te kunnen zien.');
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

        return true;
    }

    /**
     * Get the roles that are allowed to view all
     * unvalidated finds
     *
     * @return array
     */
    protected function getRolesForUnvalidated()
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
    protected function getRolesForEmbargo()
    {
        return [
            'administrator',
            'researcher',
            'agency'
        ];
    }
}
