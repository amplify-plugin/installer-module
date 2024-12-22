<?php

namespace Amplify\System\Installer\Controllers;

use Amplify\System\Installer\Helpers\PermissionsChecker;
use Illuminate\Routing\Controller;

class PermissionsController extends Controller
{
    /**
     * @var PermissionsChecker
     */
    protected $permissions;

    public function __construct(PermissionsChecker $checker)
    {
        $this->permissions = $checker;
    }

    /**
     * Display the permissions check page.
     *
     * @return \Illuminate\View\View
     */
    public function permissions()
    {
        $permissions = $this->permissions->check(
            config('installer.permissions')
        );

        return view('installer::permissions', compact('permissions'));
    }
}
