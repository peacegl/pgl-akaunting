<?php

return [
    
    'fallback' => [
        'double-entry' => [
            'accounts_receivable'           => env('SETTING_FALLBACK_DOUBLE_ENTRY_ACCOUNTS_RECEIVABLE', 120),
            'accounts_payable'              => env('SETTING_FALLBACK_DOUBLE_ENTRY_ACCOUNTS_PAYABLE', 200),
            'accounts_expenses'             => env('SETTING_FALLBACK_DOUBLE_ENTRY_ACCOUNTS_EXPENSES', 628),
            'accounts_sales'                => env('SETTING_FALLBACK_DOUBLE_ENTRY_ACCOUNTS_SALES', 400),
            'accounts_sales_discount'       => env('SETTING_FALLBACK_DOUBLE_ENTRY_ACCOUNTS_SALES_DISCOUNT', 825),
            'accounts_purchase_discount'    => env('SETTING_FALLBACK_DOUBLE_ENTRY_ACCOUNTS_PURCHASE_DISCOUNT', 475),
            'accounts_owners_contribution'  => env('SETTING_FALLBACK_DOUBLE_ENTRY_ACCOUNTS_OWNERS_CONTRIBUTION', 300),
            'accounts_payroll'              => env('SETTING_FALLBACK_DOUBLE_ENTRY_ACCOUNTS_PAYROLL', 664),
            'types_bank'                    => env('SETTING_FALLBACK_DOUBLE_ENTRY_TYPES_BANK', 6),
            'types_tax'                     => env('SETTING_FALLBACK_DOUBLE_ENTRY_TYPES_TAX', 17),
            'number_prefix'                 => env('SETTING_FALLBACK_DOUBLE_ENTRY_NUMBER_PREFIX', 'MJE-'),
            'number_digit'                  => env('SETTING_FALLBACK_DOUBLE_ENTRY_NUMBER_DIGIT', '5'),
            'number_next'                   => env('SETTING_FALLBACK_DOUBLE_ENTRY_NUMBER_NEXT', '1'),
        ],
    ],
];