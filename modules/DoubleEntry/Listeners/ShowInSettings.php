<?php

namespace Modules\DoubleEntry\Listeners;

use App\Events\Menu\SettingsCreated as Event;
use App\Traits\Modules;
use App\Traits\Permissions;

class ShowInSettings
{
    use Modules, Permissions;

    /**
     * Handle the event.
     *
     * @param  Event $event
     * @return void
     */
    public function handle(Event $event)
    {
        if ($this->moduleIsDisabled('double-entry')) {
            return;
        }

        $title = trim(trans('double-entry::general.name'));

        if (!$this->canAccessMenuItem($title, 'read-double-entry-settings')) {
            return;
        }

        $event->menu->route('double-entry.settings.edit', $title, [], 110, ['icon' => 'balance', 'search_keywords' => trans('double-entry::general.search_keywords')]);
    }
}
