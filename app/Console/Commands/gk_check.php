<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class gk_check extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gk:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sprawdź konfigurację witryny';

    protected $filter_to = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $start_time = time();
        $this->info('Start: '.date('Y-m-d H:i:s', $start_time));

        // get authentication provider
        $user_class = config('auth.providers.users.model');
        $this->info('User class: '.$user_class);

        if (str_contains($user_class, 'Models') === true) {
            $users_count = \App\Models\User::count();
        } else {
            $users_count = \App\User::count();
        }

        //check if there is at least one user
        if ($users_count == 0) {
            $this->error('Brak użytkowników w bazie danych');
        } else {
            $this->info('Liczba użytkowników: '.$users_count);
        }

        // check if environment is production
        if (config('app.env') == 'production') {
            $this->warn('Środowisko: production');
        } else {
            $this->info('Środowisko: '.config('app.env'));
        }

        // check ASSET_URL in .env
        if (config('app.asset_url') == '') {
            $this->warn('Brak ASSET_URL w pliku .env');
        } else {
            $this->info('ASSET_URL: '.config('app.asset_url'));
        }

        // check if debug is enabled
        if (config('app.debug') == true) {
            $this->warn('Debug: włączony');
        } else {
            $this->info('Debug: wyłączony');
        }

        // check if log channel is daily
        if (config('logging.default') == 'daily') {
            $this->info('Logowanie: daily');
        } else {
            $this->warn('Logowanie: '.config('logging.default'));
        }

        // check if HTTPS Only Cookies are enabled
        if (config('session.secure')) {
            $this->info('HTTPS Only Cookies: włączone');
        } else {
            $this->warn('HTTPS Only Cookies: wyłączone');
        }

        // check if timezone set to Europe/Warsaw
        if (config('app.timezone') == 'Europe/Warsaw') {
            $this->info('Strefa czasowa: Europe/Warsaw');
        } else {
            $this->warn('Strefa czasowa: '.config('app.timezone'));
        }

        $this->info('End: '.date('Y-m-d H:i:s', time()));
        $this->info('Duration: '.(time() - $start_time).'s');
        return 0;
    }
}
