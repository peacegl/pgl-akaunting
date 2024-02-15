<?php

return [

    'name'                      => 'Dubbel Bokföring',
    'description'               => 'Kontoplan, bokföringsorder, huvudbok, med mera',
    'search_keywords'           => 'diagram, konton, kontoplan, manual, liggare, bokföringspost',

    'chart_of_accounts'         => 'Kontoplan|Kontoplaner',
    'ledgers'                   => 'Huvudbok|Huvudböcker',
    'bank_accounts'             => 'Konto|Konton',
    'tax_rates'                 => 'Momssats|Momssatser',
    'parents'                   => 'Överordnad|Överordnade',
    'journals'                  => 'Grundbok|Grundböcker',
    'manual_journals'           => 'Manuell Bokföringspost|Manuella Bokföringsposter',
    'entries'                   => 'Post|Poster',
    'debits'                    => 'Debet|Debet',
    'credits'                   => 'Kredit|Kredit',

    'accounting'                => 'Redovisning',
    'coa'                       => 'Kontoplan',
    'general_ledger'            => 'Huvudbok',
    'balance_sheet'             => 'Balansräkning',
    'trial_balance'             => 'Råbalans',
    'total_type'                => 'Total :type',
    'totals_balance'            => 'Summor och utgående balans',
    'balance_change'            => 'Balansförändring',
    'bank_cash'                 => 'Likvida tillgångar',
    'default_type'              => 'Standard :type',
    'current_year_earnings'     => 'Resultat för innevarande år',
    'liabilities_equities'      => 'Skulder & Eget kapital',
    'edit_account'              => 'Redigera :type konto',
    'issued'                    => 'Utfärdad',
    'sub'                       => 'Under',
    'journals_description'      => 'Poster i grundboken skapas med debet och kredit rader för att reflektera huvudboken.',

    'accounts' => [
        'receivable'            => 'Kundfordran',
        'payable'               => 'Leverantörsskuld',
        'sales'                 => 'Försäljning',
        'expenses'              => 'Allmänna kostnader',
        'sales_discount'        => 'Försäljningsrabatt',
        'purchase_discount'     => 'Köprabatt',
        'owners_contribution'   => 'Ägartillskott',
        'payroll'               => 'Lönelista',
    ],

    'document' => [
        'detail'                => 'Ett :class-konto används för korrekt bokföring av din :type och för att hålla dina rapporter korrekta.',
    ],

    'empty' => [
        'journal_entry'         => 'En bokföringspost är en notering i grundboken, där man för journal över alla affärshändelser. Bokföringsposten kan bestå av flera noteringar som var och en antingen är en debet eller en kredit och beskriver en affärshändelse.',
    ],

    'form_description' => [
        'manual_journal'        => [
            'general'           => 'Här kan du ange allmän information om den manuella bokföringsposten såsom datum, verifikationsnummer, valuta, beskrivning etc.',
            'items'             => 'Här kan du ange noteringar i den manuella bokföringsposten såsom konto, debet, kredit, etc.',
        ],
        'chart_of_accounts' => [
            'general'           => 'Här kan du ange all information om en kontoplan.',
        ],
    ],

];
