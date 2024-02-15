<?php

namespace Modules\DoubleEntry\Listeners;

use App\Traits\Jobs;
use Modules\DoubleEntry\Jobs\Install\CopyData;
use App\Events\Module\Enabled as Event;

class ModuleEnabled
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

        $this->copyData();
    }

    protected function copyData()
    {
        $this->dispatch(new CopyData());
    }
}
