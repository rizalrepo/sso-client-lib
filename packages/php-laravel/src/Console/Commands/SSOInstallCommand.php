<?php

namespace Rizalrepo\SsoClient\Console\Commands;

use Illuminate\Console\Command;

class SSOInstallCommand extends Command
{
    protected $signature = 'sso:install {--force : Overwrite any existing published files}';

    protected $description = 'Publish UNISM SSO config and optional controller stub';

    public function handle(): int
    {
        $this->info('Publishing SSO config + controller stub...');

        $this->call('vendor:publish', [
            '--tag' => 'sso-config',
            '--force' => (bool) $this->option('force'),
        ]);

        $this->newLine();
        $this->info('Add these to your .env (if not already set):');
        $this->line('SSO_URL=');
        $this->line('SSO_CLIENT_ID=');
        $this->line('SSO_CLIENT_SECRET=');
        $this->line('SSO_CALLBACK_URL=' . rtrim(config('app.url'), '/') . '/callback');
        $this->newLine();
        $this->line('Then visit `GET /sso/login` to start the flow.');

        return self::SUCCESS;
    }
}

