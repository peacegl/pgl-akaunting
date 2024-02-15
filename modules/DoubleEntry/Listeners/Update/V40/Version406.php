<?php

namespace Modules\DoubleEntry\Listeners\Update\V40;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use Illuminate\Support\Facades\Artisan;

class Version406 extends Listener
{
    const ALIAS = 'double-entry';

    const VERSION = '4.0.6';

    /**
     * Handle the event.
     *
     * @param  $event
     * @return void
     */
    public function handle(UpdateFinished $event)
    {
        if ($this->skipThisUpdate($event)) {
            return;
        }

        $this->updateDatabase();
    }

    protected function updateDatabase()
    {
        Artisan::call('module:migrate', ['alias' => self::ALIAS, '--force' => true]);
    }
}
