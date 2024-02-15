<?php

return [

    'name'                      => 'Double-Entry',
    'description'               => 'Chart of Accounts, Manual Journal, General Ledger, and more',
    'search_keywords'           => 'chart, accounts, coa, manual, journal, entry',

    'chart_of_accounts'         => 'Chart of Account|Chart of Accounts',
    'ledgers'                   => 'Ledger|Ledgers',
    'bank_accounts'             => 'Account|Accounts',
    'tax_rates'                 => 'Tax Rate|Tax Rates',
    'parents'                   => 'Parent|Parents',
    'journals'                  => 'Journal|Journals',
    'manual_journals'           => 'Manual Journal|Manual Journals',
    'journal_entry'             => 'Manual Journal|Manual Journals', // TODO: remove
    'entries'                   => 'Entry|Entries',
    'debits'                    => 'Debit|Debits',
    'credits'                   => 'Credit|Credits',
    'lines'                     => 'Line|Lines',
    'relations'                 => 'Relation|Relations',

    'accounting'                => 'Accounting',
    'coa'                       => 'COA',
    'general_ledger'            => 'General Ledger',
    'balance_sheet'             => 'Balance Sheet',
    'trial_balance'             => 'Trial Balance',
    'total_type'                => 'Total :type',
    'totals_balance'            => 'Totals and Closing Balance',
    'balance_change'            => 'Balance Change',
    'bank_cash'                 => 'Bank and Cash',
    'default_type'              => 'Default :type',
    'current_year_earnings'     => 'Current Year Earnings',
    'liabilities_equities'      => 'Liabilities & Equities',
    'edit_account'              => 'Edit :type Account',
    'issued'                    => 'Issued',
    'sub'                       => 'Sub',
    'journals_description'      => 'Journals are created with debit and credit entries to reflect in General Ledger.',
    'opening_balance'           => 'Opening Balance',
    'parent_account'            => 'Parent Account',

    'accounts' => [
        'receivable'            => 'Accounts Receivable',
        'payable'               => 'Accounts Payable',
        'sales'                 => 'Sales',
        'expenses'              => 'General Expenses',
        'sales_discount'        => 'Sales Discount',
        'purchase_discount'     => 'Purchase Discount',
        'owners_contribution'   => 'Owners Contribution',
        'payroll'               => 'Payroll',
    ],

    'document' => [
        'detail'                => 'An :class account is used for proper bookkeeping of your :type and to keep your reports accurate.',
    ],

    'empty' => [
        'journal_entry'         => 'Manual journals allow you to record debit and credit entries for unique financial transactions manually. The journal entry can consist of several recordings, each of which is either a debit or a credit.',
    ],

    'form_description' => [
        'manual_journal'        => [
            'general'           => 'Here you can enter the general information of the journal entry, such as date, currency, and description.',
            'lines'             => 'Here you can enter the lines of the journal entry such as account, debit, credit, etc.',
            'other'             => 'Enter a number and reference to keep the journal entry linked to your records.',
        ],
        'chart_of_accounts' => [
            'general'           => 'Here you can enter all information related to a chart of account.',
        ],
    ],

];
