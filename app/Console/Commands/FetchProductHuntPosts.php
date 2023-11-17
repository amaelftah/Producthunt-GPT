<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Actions\SyncProductHuntPosts;

class FetchProductHuntPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-product-hunt-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fetches product hunt top ranked posts from producthunt and populates the db';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        (new SyncProductHuntPosts)->execute();
    }
}
