<?php

namespace Amplify\System\Installer\Controllers;

use Amplify\System\Installer\Events\LaravelInstallerFinished;
use Amplify\System\Installer\Helpers\EnvironmentManager;
use Amplify\System\Installer\Helpers\FinalInstallManager;
use Amplify\System\installer\Helpers\InstalledFileManager;
use Illuminate\Routing\Controller;

class FinalController extends Controller
{
    /**
     * Update installed file and display finished view.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function finish(InstalledFileManager $fileManager, FinalInstallManager $finalInstall, EnvironmentManager $environment)
    {
        $finalMessages = $finalInstall->runFinal();
        $finalStatusMessage = $fileManager->update();
        $finalEnvFile = $environment->getEnvContent();

        event(new LaravelInstallerFinished);

        return view('installer::finished', compact('finalMessages', 'finalStatusMessage', 'finalEnvFile'));
    }
}
