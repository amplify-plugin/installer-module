<?php

namespace Amplify\System\Installer\Controllers;

use Amplify\System\Installer\Helpers\RequirementsChecker;
use Illuminate\Routing\Controller;

class RequirementsController extends Controller
{
    /**
     * @var RequirementsChecker
     */
    protected $requirements;

    public function __construct(RequirementsChecker $checker)
    {
        $this->requirements = $checker;
    }

    /**
     * Display the requirements page.
     *
     * @return \Illuminate\View\View
     */
    public function requirements()
    {
        $phpSupportInfo = $this->requirements->checkPHPversion(
            config('installer.core.minPhpVersion')
        );
        $requirements = $this->requirements->check(
            config('installer.requirements')
        );

        return view('installer::requirements', compact('requirements', 'phpSupportInfo'));
    }
}
