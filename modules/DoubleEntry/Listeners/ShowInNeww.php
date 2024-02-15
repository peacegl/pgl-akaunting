<?php

namespace Modules\DoubleEntry\Listeners;

use Akaunting\Menu\MenuBuilder;
use App\Events\Menu\NewwCreated as Event;
use App\Traits\Modules;
use App\Traits\Permissions;

class ShowInNeww
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

        /** @var MenuBuilder $menu */
        $menu = $event->menu;

        $title = trim(trans_choice('double-entry::general.manual_journals', 1));

        if ($this->canAccessMenuItem($title, 'create-double-entry-journal-entry')) {
            $menu->route('double-entry.journal-entry.create', $title, [], 80, ['icon' => 'balance']);
        }
    }
}
