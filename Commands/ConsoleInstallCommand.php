<?php

namespace Amplify\System\Installer\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ConsoleInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amplify:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function setEnv($key, $value)
    {
        file_put_contents(app()->environmentFilePath(), str_replace(
            $key.'="'.env($key).'"',
            $key.'="'.$value.'"',
            file_get_contents(app()->environmentFilePath())
        ));
    }

    protected function validate($fields, $rules, $recursion)
    {
        $validator = Validator::make($fields, $rules);

        if ($validator->fails()) {
            $this->error(implode(' ', $validator->errors()->all()));
            $this->{$recursion}();

            return true;
        }

        return false;
    }

    protected function checkStoragePermission()
    {
        $error = function () {
            $this->error("Storage folder doesn't have read write and executable permissions.");
            exit();
        };

        $hasPermission = function ($path) {
            return is_readable($path) && is_writable($path) && is_executable($path);
        };

        try {
            if (! $hasPermission(app()->basePath('bootstrap/cache')) || ! $hasPermission(app()->basePath('storage/logs')) || ! $hasPermission(app()->basePath('storage/framework'))) {
                $error();
            }
        } catch (\Throwable $th) {
            $error();
        }
    }

    protected function setupEnv()
    {
        copy('.env.example', '.env');
        Artisan::call('key:generate');
    }

    protected function setupAppInfo()
    {
        $app_name = $this->ask('Enter your app name');
        $app_url = $this->ask('Enter your app domain');

        $isNotValidated = $this->validate([
            'app_name' => $app_name,
            'app_url' => $app_url,
        ], [
            'app_name' => 'required',
            'app_url' => 'required|url',
        ], 'setupAppInfo');

        if ($isNotValidated) {
            return 0;
        }

        $this->setEnv('APP_NAME', $app_name);
        $this->setEnv('APP_URL', $app_url);
    }

    protected function setupDatabaseToEnv()
    {
        try {
            // Getting database credintials from user.
            $this->info('Configure your database.');
            $db_host = $this->ask('What is your database host?');
            $db_port = $this->ask('What is your database port?');
            $db_database_name = $this->ask('What is your database name?');
            $db_user = $this->ask('What is your database user name?');
            $db_password = $this->secret('What is your database user password?');

            $isNotValidated = $this->validate([
                'host' => $db_host,
                'port' => $db_port,
                'database_name' => $db_database_name,
                'user' => $db_user,
                'password' => $db_password,
            ], [
                'host' => 'required',
                'port' => 'required|integer',
                'database_name' => 'required',
                'user' => 'required',
                'password' => 'required',
            ], 'setupDatabaseToEnv');

            if ($isNotValidated) {
                return 0;
            }

            // Checking database credintials is correct.
            if (! mysqli_connect($db_host, $db_user, $db_password, $db_database_name, $db_port)) {
                $this->error('Could not connect to database.');
                $this->setupDatabaseToEnv();
            } else {
                // Setting up database credintials to our env.
                $this->setEnv('DB_HOST', $db_host);
                $this->setEnv('DB_PORT', $db_port);
                $this->setEnv('DB_DATABASE', $db_database_name);
                $this->setEnv('DEFAULT_DB', $db_database_name);
                $this->setEnv('DB_USERNAME', $db_user);
                $this->setEnv('DB_PASSWORD', $db_password);

                Config::set('database.connections.mysql', [
                    'driver' => 'mysql',
                    'host' => $db_host,
                    'port' => $db_port,
                    'database' => $db_database_name,
                    'username' => $db_user,
                    'password' => $db_password,
                ]);

                DB::purge('mysql');
                DB::reconnect('mysql');
            }

        } catch (\Throwable $th) {
            $this->error($th->getMessage());
            $this->setupDatabaseToEnv();
        }
    }

    public function dbMigrate()
    {
        try {
            $this->info('Migrating your database...');
            Artisan::call('migrate --seed');
            $this->info('Migration complete.');
        } catch (\Throwable $th) {
            $this->error('Something went wrong with migration.');
            exit();
        }
    }

    protected function createSuperAdmin()
    {
        $name = $this->ask('Enter super admin name');
        $email = $this->ask('Enter super admin email');
        $password = $this->secret('Enter super admin password');

        $isNotValidated = $this->validate([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ], 'createSuperAdmin');

        if ($isNotValidated) {
            return 0;
        }

        $admin = User::create([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make($password),
            'is_admin' => '1',
            'remember_token' => Str::random(10),
        ]);

        $admin->assignRole('Super Admin');
    }

    protected function setupMailServerToEnv()
    {
        // Getting mail credintials from user.
        $this->info('Configure your mail.');
        $mail_driver = $this->choice('What is your mail driver?', ['smtp', 'mailgun', 'sendmail']);
        $mail_mailer = $this->choice('What is your mail mailer?', ['smtp', 'mailgun', 'sendmail']);
        $mail_host = $this->ask('What is your mail host?');
        $mail_port = $this->ask('What is your mail port?');
        $mail_username = $this->ask('What is your mail username?');
        $mail_password = $this->ask('What is your mail password?');
        $mail_encryption = $this->choice('What is your mail encryption?', ['tls', 'ssl']);
        $mail_from_address = $this->ask('What is your mail from_address?');
        $mail_from_name = $this->ask('What is your mail from_name?');

        $isNotValidated = $this->validate([
            'mail_driver' => $mail_driver,
            'mail_mailer' => $mail_mailer,
            'mail_host' => $mail_host,
            'mail_port' => $mail_port,
            'mail_username' => $mail_username,
            'mail_password' => $mail_password,
            'mail_encryption' => $mail_encryption,
            'mail_from_address' => $mail_from_address,
            'mail_from_name' => $mail_from_name,
        ], [
            'mail_driver' => 'required',
            'mail_mailer' => 'required',
            'mail_host' => 'required',
            'mail_port' => 'required|integer',
            'mail_username' => 'required',
            'mail_password' => 'required',
            'mail_encryption' => 'required',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required',
        ], 'setupMailServerToEnv');

        if ($isNotValidated) {
            return 0;
        }

        // Configurating up mail credintials to env.
        $this->setEnv('MAIL_DRIVER', $mail_driver);
        $this->setEnv('MAIL_MAILER', $mail_mailer);
        $this->setEnv('MAIL_HOST', $mail_host);
        $this->setEnv('MAIL_PORT', $mail_port);
        $this->setEnv('MAIL_USERNAME', $mail_username);
        $this->setEnv('MAIL_PASSWORD', $mail_password);
        $this->setEnv('MAIL_ENCRYPTION', $mail_encryption);
        $this->setEnv('mail_from_address', $mail_from_address);
        $this->setEnv('MAIL_FROM_NAME', $mail_from_name);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Check storage folder permission.
        $this->checkStoragePermission();

        // Init env file.
        $this->setupEnv();

        // Setup app information.
        $this->setupAppInfo();

        // Setuping database to our application.
        $this->setupDatabaseToEnv();

        // Migrate database.
        $this->dbMigrate();

        // Create Super Admin
        $this->createSuperAdmin();

        // Configurating mail to env.
        // $this->setupMailServerToEnv();

        $this->info('Successfully completed configuration.');

        return 0;
    }
}
