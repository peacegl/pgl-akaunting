<?php

Route::admin('double-entry', function () {
    Route::get('chart-of-accounts/{chart_of_account}/enable', 'ChartOfAccounts@enable')->name('chart-of-accounts.enable');
    Route::get('chart-of-accounts/{chart_of_account}/disable', 'ChartOfAccounts@disable')->name('chart-of-accounts.disable');
    Route::get('chart-of-accounts/{chart_of_account}/duplicate', 'ChartOfAccounts@duplicate')->name('chart-of-accounts.duplicate');
    Route::post('chart-of-accounts/import', 'ChartOfAccounts@import')->name('chart-of-accounts.import');
    Route::get('chart-of-accounts/export', 'ChartOfAccounts@export')->name('chart-of-accounts.export');
    Route::resource('chart-of-accounts', 'ChartOfAccounts');

    Route::get('journal-entry/{journal_entry}/duplicate', 'JournalEntry@duplicate')->name('journal-entry.duplicate');
    Route::post('journal-entry/import', 'JournalEntry@import')->name('journal-entry.import');
    Route::get('journal-entry/export', 'JournalEntry@export')->name('journal-entry.export');
    Route::get('journal-entry/{journal_entry}/print', 'JournalEntry@printJournalEntry')->name('journal-entry.print');
    Route::get('journal-entry/{journal_entry}/pdf', 'JournalEntry@pdfJournalEntry')->name('journal-entry.pdf');
    Route::resource('journal-entry', 'JournalEntry', ['middleware' => ['double-entry-money']]);

    Route::get('settings', 'Settings@edit')->name('settings.edit');
    Route::post('settings', 'Settings@update')->name('settings.update');
});
