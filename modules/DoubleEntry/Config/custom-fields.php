<?php

use Modules\DoubleEntry\Models\Account as COA;
use Modules\DoubleEntry\Models\Journal;

return [
    COA::class => [
        [
            'location'    => [
                'code' => 'double-entry-chart-of-accounts',
                'name' => 'double-entry::general.chart_of_accounts',
            ],
            'sort_orders' => [
                'name'          => 'general.name',
                'code'          => 'general.code',
                'type_id'       => ['general.types', 1],
                'account_id'    => 'double-entry::general.parent_account',
                'description'   => 'general.description',
            ],
            'views'       => [
                'crud'       => [
                    'double-entry::chart_of_accounts.create',
                    'double-entry::chart_of_accounts.edit',
                ],
                'show'       => [
                ],
            ],
            'export' => 'Modules\DoubleEntry\Exports\COA',
            'tests' => [
                'factory'   => ['enabled'],
                'routes'    => [
                    'get'       => 'double-entry.chart-of-accounts.create',
                    'post'      => 'double-entry.chart-of-accounts.store',
                    'patch'     => 'double-entry.chart-of-accounts.update',
                    'delete'    => 'double-entry.chart-of-accounts.destroy',
                ],
            ],
        ],
    ],

    Journal::class => [
        [
            'location'      => [
                'code' => 'double-entry-journal-entry',
                'name' => 'double-entry::general.manual_journals',
            ],
            'sort_orders'   => [
                'paid_at'           => 'general.date',
                'currency_code'     => ['general.currencies', 1],
                'description'       => 'general.description',
                'journal_number'    => ['general.numbers', 1],
                'basis'             => 'general.basis',
                'reference'         => 'general.reference',
            ],
            'views'         => [
                'crud'  => [
                    'double-entry::journal_entry.create',
                    'double-entry::journal_entry.edit',
                ],
                'show'  => [
                    'double-entry::journal_entry.show',
                    'double-entry::partials.journal_show_print'
                ],
            ],
            'export' => [
                'Modules\DoubleEntry\Exports\JournalEntry\Journals',
                'Modules\DoubleEntry\Exports\JournalEntry\JournalLedgers',
            ],
            'tests' => [
                'factory'   => ['enabled'],
                'routes'    => [
                    'get'       => 'double-entry.journal-entry.create',
                    'post'      => 'double-entry.journal-entry.store',
                    'patch'     => 'double-entry.journal-entry.update',
                    'delete'    => 'double-entry.journal-entry.destroy',
                ],
            ],
        ],
    ],
];
