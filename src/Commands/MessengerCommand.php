<?php

namespace Messenger\Chat\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Illuminate\Support\Facades\File;

class MessengerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messenger:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function handle()
    {
        $this->exportBackend();

        $this->info('Package installed.');
    }

    /**
     * Export the authentication backend.
     *
     * @return void
     */
    protected function exportBackend()
    {
        if (stripos(file_get_contents(base_path('routes/web.php')), 'Messenger\Chat\MessengerRoutes::routes();') === false) {
            $routes = "\n\nMessenger\Chat\MessengerRoutes::routes();\n\n";
            File::append(base_path('routes/web.php'), $routes);
        }

        if (stripos(file_get_contents(base_path('routes/channels.php')), 'Messenger\Chat\MessengerRoutes::channels();') === false) {
            $channels = "\n\nMessenger\Chat\MessengerRoutes::channels();\n\n";
            File::append(base_path('routes/channels.php'), $channels);
        }
        
    }
}
