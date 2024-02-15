<?php

namespace Modules\DoubleEntry\Listeners;

use App\Traits\Jobs;
use Modules\DoubleEntry\Jobs\Install\CopyData;
use Illuminate\Support\Facades\Artisan;
use App\Events\Module\Installed as Event;

class FinishInstallation
{
    use Jobs;

    /**
     * Handle the event.
     *
     * @param  Event $event
     * @return void
     */
    public function handle(Event $event)
    {
        if ($event->alias != 'double-entry') {
            return;
        }

        $this->callSeeds();

        $this->copyData();
    }

    protected function callSeeds()
    {
        Artisan::call('company:seed', [
            'company' => company_id(),
            '--class' => 'Modules\DoubleEntry\Database\Seeds\Install',
        ]);
    }

    protected function copyData()
    {
        $this->dispatch(new CopyData());
    }
}
