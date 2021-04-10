<?php


namespace App;


class NodeConstants
{
    /**
     * NOTE: DO NOT precede the tenant label with double underscores, it doesn't see it as a property you can query on
     * For example: __MEDEA_TENANT_LABEL will not return an expected result when using it with NOT EXISTS or IS NULL
     *
     * @var string
     */
    const TENANT_LABEL = 'MEDEA_TENANT_LABEL';
}
