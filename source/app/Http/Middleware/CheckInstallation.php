<?php

namespace App\Http\Middleware;

use App\Helpers\InstallerHelper;
use Closure;

class CheckInstallation
{
    public function handle($request, Closure $next)
    {
        // Check and generate APP_KEY before Laravel boots
        InstallerHelper::checkAndGenerateAppKey();

        return $next($request);
    }
}
