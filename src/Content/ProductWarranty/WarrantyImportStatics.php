<?php

declare(strict_types=1);

namespace Sas\WarrantyManager\Content\ProductWarranty;

/**
 * We need some statics during import. This class provides them.
 * Be careful to change the value of these statics. It might cause a break to import functionality
 */
final class WarrantyImportStatics
{
    /*
     * Unique name for the Warranty profile.
     * This value is used within the admin components as well and
     * should not be modified
     */
    public const PROFILE_NAME = 'Warranty import profile';
}
