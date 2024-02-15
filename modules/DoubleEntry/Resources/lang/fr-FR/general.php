<?php

return [

    'name' => 'Double-entrée',
    'description' => 'Plan de comptes, saisie sur journal, grand-livre, et autres',

    'accounting' => 'Comptabilité',
    'chart_of_accounts' => 'Plan comptable|Plans comptables',
    'coa' => 'PC',
    'journal_entry' => 'Entrée de journal',
    'general_ledger' => 'Grand-Livre',
    'balance_sheet' => 'Balance comptable',
    'trial_balance' => 'Balance',
    'journal_report' => 'Journal manuel',
    'account' => 'Compte',
    'debit' => 'Débit',
    'credit' => 'Crédit',
    'total_type' => 'Total :type',
    'totals_balance' => 'Totaux et Solde de clôture',
    'balance_change' => 'Changement de solde',
    'bank_cash' => 'Banque et espèces',
    'default_type' => 'Default :type',
    'current_year_earnings' => 'Gains de l\'année en cours',
    'liabilities_equities' => 'Passifs et fonds propres',
    'ledgers' => 'Registre|Registres',
    'bank_accounts' => 'Compte|Comptes',
    'tax_rates' => 'Taux d’imposition|Taux d’imposition',
    'edit_account' => 'Modifier le compte :type',
    'issued' => 'Publié',
    'sub' => 'Sous',
    'parents' => 'Parent|Parents',
    'journals' => 'Journal|Journaux',
    'entries' => 'Entrée|Entrées',
    'search_keywords' => 'Plan comptable par défaut, saisie manuelle du journal',
    'journals_description' => 'Les journaux sont créés avec des entrées de débit et de crédit pour refléter dans le Grand livre général.',

    'accounts' => [
        'receivable' => 'Comptes clients',
        'payable' => 'Comptes fournisseurs',
        'sales' => 'Ventes',
        'expenses' => 'Dépenses générales',
        'sales_discount' => 'Remise sur les ventes',
        'purchase_discount' => 'Remise sur achat',
        'owners_contribution' => 'Contribution des propriétaires',
    ],

    'document' => [
        'detail' => 'Un compte :class est utilisé pour la comptabilité correcte de votre :type et pour garder vos rapports exacts.',
    ],

    'empty' => [
        'manual_journal' => 'Une inscription au journal est l\'acte de conserver ou de faire des registres de toute opération. L\'entrée du journal peut se composer de plusieurs enregistrements, chacun étant soit un débit, soit un crédit.',
    ],

    'form_description' => [
        'manual_journal' => [
            'general' => 'Ici, vous pouvez entrer les informations générales du journal manuel tels que la date, le numéro, la devise, la description, etc.',
            'items' => 'Ici, vous pouvez entrer les éléments du journal manuel tels que le compte, le débit, le crédit, etc.',
        ],
        'chart_of_accounts' => [
            'general' => 'Ici vous pouvez entrer toutes les informations relatives à un plan comptable.',
        ],
    ],

];
