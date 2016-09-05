<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\UserRepository;
use App\Repositories\NotificationRepository;

class SendNotificationsForSavedSearches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medea:notify-saved-searches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes the users with saved searches and creates a notification if they have not seen the previous one for every saved search.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UserRepository $users, NotificationRepository $notifications)
    {
        parent::__construct();

        $this->users = $users;
        $this->notifications = $notifications;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = $this->users->getAllWithSavedSearches();

        foreach ($users as $user) {
            foreach ($user['searches'] as $search) {
                // Check if an unread notification for the url, based on the filters, exists
                // if not, then create a new notification
                $uri = $this->buildFilterUri($search['filter']);

                // Get the latest notification for the user for the given uri
                $latestNotification = $this->notifications->getLatestByUri($uri, $user['user_id']);

                if (empty($latestNotification) || (!empty($latestNotification) && $latestNotification['read'] == 1)) {
                    // The latest notification has been read, make a new one
                    $this->notifications->store([
                        'user_id' => $user['user_id'],
                        'url' => $uri,
                        'message' => 'Er zijn nieuwe vondsten voor uw ingestelde filter (' . $search['name'] . ')',
                    ]);
                } else {
                    $this->line("The user already has a notification for the filter " . $search['name']);
                }
            }
        }
    }

    private function buildFilterUri($filters)
    {
        $uri = url('/finds?');

        foreach ($filters as $filterName => $filterValue) {
            $uri .= $filterName . '=' . $filterValue . '&';
        }

        return rtrim($uri, '&');
    }
}
