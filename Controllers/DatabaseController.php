<?php

namespace Amplify\System\Installer\Controllers;

use Amplify\System\Installer\Helpers\DatabaseManager;
use Illuminate\Routing\Controller;

class DatabaseController extends Controller
{
    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Migrate and seed the database.
     *
     * @return \Illuminate\View\View
     */
    public function database()
    {
        $response = $this->databaseManager->migrateAndSeed();

        return redirect()->route('installer.final')
            ->with(['message' => $response]);
    }
}
