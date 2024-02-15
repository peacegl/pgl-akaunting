<?php

namespace Modules\DoubleEntry\Jobs\Install;

use App\Abstracts\Job;
use App\Traits\Documents;
use App\Models\Setting\Tax;
use App\Models\Banking\Account;
use App\Models\Banking\Transfer;
use App\Models\Document\Document;
use App\Models\Banking\Transaction;
use Modules\DoubleEntry\Models\Ledger;
use Modules\DoubleEntry\Models\AccountTax;
use Modules\DoubleEntry\Models\AccountBank;
use Modules\DoubleEntry\Models\Account as Coa;
use Modules\DoubleEntry\Traits\Accounts;
use Modules\DoubleEntry\Jobs\Journal\CreateJournalEntry;

class CopyData extends Job
{
    use Accounts, Documents;

    public function handle()
    {
        \DB::transaction(function () {
            $this->copyAccounts();
            $this->copyTransfers();
            $this->copyTaxes();
            $this->copyInvoices();
            $this->copyIncomeTransactions();
            $this->copyBills();
            $this->copyExpenseTransactions();
        });
    }

    /**
     * Copy existing banking accounts to the chart of accounts.
     *
     * @return void
     */
    protected function copyAccounts()
    {
        foreach (Account::lazy() as $account) {
            $account_bank = AccountBank::where('bank_id', $account->id)->first();

            if (is_null($account_bank)) {
                $this->createBankAccount($account);

                continue;
            }

            $account_bank->account->update([
                'name' => $account->name,
                'enabled' => $account->enabled,
            ]);
        }
    }

    /**
     * Copy existing transfers to the journals.
     *
     * @return void
     */
    protected function copyTransfers()
    {
        Transfer::cursor()->each(function ($transfer) {
            $payment = $transfer->expense_transaction;
            $revenue = $transfer->income_transaction;
    
            $payment_account_id = AccountBank::where('bank_id', $payment->account_id)->pluck('account_id')->first();
            $revenue_account_id = AccountBank::where('bank_id', $revenue->account_id)->pluck('account_id')->first();
    
            if (empty($payment_account_id) || empty($revenue_account_id)) {
                return;
            }
    
            // $journal_number = $this->getNextJournalNumber();
    
            // $journal = Journal::where('company_id', $transfer->company_id)
            //     ->where('reference', 'transfer:' . $transfer->id)
            //     ->where('journal_number', $journal_number)
            //     ->first();
    
            // if (! $journal) {
            //     $journal = Journal::create([
            //         'company_id' => $transfer->company_id,
            //         'reference' => 'transfer:' . $transfer->id,
            //         'journal_number' => $journal_number,
            //         'amount' => $payment->amount,
            //         'currency_code' => $payment->currency_code,
            //         'currency_rate' => $payment->currency_rate,
            //         'paid_at' => $payment->paid_at,
            //         'description' => $payment->description ?: trans_choice('general.transfers', 1),
            //     ]);
    
            //     event(new JournalCreated($journal));
            // }
    
            $l1 = Ledger::updateOrCreate([
                'company_id' => $transfer->company_id,
                'ledgerable_type' => get_class($payment),
                'ledgerable_id' => $payment->id,
                'account_id' => $payment_account_id,
            ], [
                'issued_at' => $payment->paid_at,
                'entry_type' => 'item',
                'credit' => $payment->amount,
                'reference' => 'transfer:' . $transfer->id,
            ]);
    
            $payment->reference = 'journal-entry-ledger:' . $l1->id;
            $payment->save();
    
            $l2 = Ledger::updateOrCreate([
                'company_id' => $transfer->company_id,
                'ledgerable_type' => get_class($revenue),
                'ledgerable_id' => $revenue->id,
                'account_id' => $revenue_account_id,
            ], [
                'issued_at' => $revenue->paid_at,
                'entry_type' => 'item',
                'debit' => $revenue->amount,
                'reference' => 'transfer:' . $transfer->id,
            ]);
    
            $revenue->reference = 'journal-entry-ledger:' . $l2->id;
            $revenue->save();
        });
    }

    /**
     * Copy existing taxes to the chart of accounts.
     *
     * @return void
     */
    protected function copyTaxes()
    {
        foreach (Tax::lazy() as $tax) {
            $account_tax = AccountTax::where('tax_id', $tax->id)->first();

            if (is_null($account_tax)) {
                $chart_of_account = Coa::create([
                    'company_id' => company_id(),
                    'type_id' => setting('double-entry.types_tax', 17),
                    'code' => $this->getNextAccountCode(),
                    'name' => $tax->name,
                    'enabled' => 1,
                ]);

                $chart_of_account->tax()->create([
                    'company_id' => company_id(),
                    'account_id' => $chart_of_account->id,
                    'tax_id' => $tax->id,
                ]);

                continue;
            }

            $account_tax->account->update([
                'name' => $tax->name,
                'enabled' => $tax->enabled,
            ]);
        }
    }

    /**
     * Copy existing invoices to the ledgers.
     *
     * @return void
     */
    protected function copyInvoices()
    {
        Document::invoice()->with(['items', 'item_taxes', 'transactions'])->cursor()->each(function ($invoice) {
            $accounts_receivable_id = Coa::code(setting('double-entry.accounts_receivable', 120))->pluck('id')->first();

            Ledger::updateOrCreate([
                'company_id' => company_id(),
                'ledgerable_type' => get_class($invoice),
                'ledgerable_id' => $invoice->id,
                'account_id' => $accounts_receivable_id,
            ], [
                'issued_at' => $invoice->issued_at,
                'entry_type' => 'total',
                'debit' => $invoice->amount,
            ]);

            $invoice->items()->each(function ($item) use ($invoice) {
                $account_id = Coa::code(setting('double-entry.accounts_sales', 400))->pluck('id')->first();

                $ledger = Ledger::where('ledgerable_type', get_class($item))->where('ledgerable_id', $item->id)->first();

                if ($ledger) {
                    $account_id = $ledger->account_id;
                }

                Ledger::updateOrCreate([
                    'company_id' => company_id(),
                    'ledgerable_type' => get_class($item),
                    'ledgerable_id' => $item->id,
                    'account_id' => $account_id,
                ], [
                    'issued_at' => $invoice->issued_at,
                    'entry_type' => 'item',
                    'credit' => $item->total,
                ]);
            });

            $invoice->item_taxes()->each(function ($item_tax) use ($invoice) {
                $account_id = AccountTax::where('tax_id', $item_tax->tax_id)->pluck('account_id')->first();

                $ledger = Ledger::where('ledgerable_type', get_class($item_tax))->where('ledgerable_id', $item_tax->id)->first();

                if ($ledger) {
                    $account_id = $ledger->account_id;
                }

                Ledger::updateOrCreate([
                    'company_id' => company_id(),
                    'ledgerable_type' => get_class($item_tax),
                    'ledgerable_id' => $item_tax->id,
                    'account_id' => $account_id,
                ], [
                    'issued_at' => $invoice->issued_at,
                    'entry_type' => 'item',
                    'credit' => $item_tax->amount,
                ]);
            });

            $invoice->transactions()->each(function ($transaction) use ($accounts_receivable_id) {
                $account_id = AccountBank::where('bank_id', $transaction->account_id)->pluck('account_id')->first();

                if (is_null($account_id)) {
                    $account = $this->createBankAccount($transaction->account);

                    $account_id = $account->id;
                }

                Ledger::updateOrCreate([
                    'company_id' => company_id(),
                    'ledgerable_type' => get_class($transaction),
                    'ledgerable_id' => $transaction->id,
                    'account_id' => $account_id,
                ], [
                    'issued_at' => $transaction->paid_at,
                    'entry_type' => 'total',
                    'debit' => $transaction->amount,
                ]);

                Ledger::updateOrCreate([
                    'company_id' => company_id(),
                    'ledgerable_type' => get_class($transaction),
                    'ledgerable_id' => $transaction->id,
                    'account_id' => $accounts_receivable_id,
                ], [
                    'issued_at' => $transaction->paid_at,
                    'entry_type' => 'item',
                    'credit' => $transaction->amount,
                ]);
            });
        });
    }

    /**
     * Copy existing transactions that type's are income to the ledgers.
     *
     * @return void
     */
    protected function copyIncomeTransactions()
    {
        Transaction::whereNot('reference', 'LIKE', 'journal-entry-ledger%')
            ->type('income')
            ->isNotDocument()
            ->isNotTransfer()
            ->cursor()
            ->each(function ($transaction) {
                $account_id = AccountBank::where('bank_id', $transaction->account_id)->pluck('account_id')->first();

                if (is_null($account_id)) {
                    $account = $this->createBankAccount($transaction->account);

                    $account_id = $account->id;
                }

                Ledger::updateOrCreate([
                    'company_id' => company_id(),
                    'ledgerable_type' => get_class($transaction),
                    'ledgerable_id' => $transaction->id,
                    'account_id' => $account_id,
                ], [
                    'issued_at' => $transaction->paid_at,
                    'entry_type' => 'total',
                    'debit' => $transaction->amount,
                ]);

                $account_id = Coa::code(setting('double-entry.accounts_sales', 400))->pluck('id')->first();

                Ledger::updateOrCreate([
                    'company_id' => company_id(),
                    'ledgerable_type' => get_class($transaction),
                    'ledgerable_id' => $transaction->id,
                    'account_id' => $account_id,
                ], [
                    'issued_at' => $transaction->paid_at,
                    'entry_type' => 'item',
                    'credit' => $transaction->amount,
                ]);
            }
        );
    }

    /**
     * Copy existing bills to the ledgers.
     *
     * @return void
     */
    protected function copyBills()
    {
        Document::bill()->with(['items', 'item_taxes', 'transactions'])->cursor()->each(function ($bill) {
            $accounts_payable_id = Coa::code(setting('double-entry.accounts_payable', 200))->pluck('id')->first();

            Ledger::updateOrCreate([
                'company_id' => company_id(),
                'ledgerable_type' => get_class($bill),
                'ledgerable_id' => $bill->id,
                'account_id' => $accounts_payable_id,
            ], [
                'issued_at' => $bill->issued_at,
                'entry_type' => 'total',
                'credit' => $bill->amount,
            ]);

            $bill->items()->each(function ($item) use ($bill) {
                $account_id = Coa::code(setting('double-entry.accounts_expenses', 628))->pluck('id')->first();

                $ledger = Ledger::where('ledgerable_type', get_class($item))->where('ledgerable_id', $item->id)->first();

                if ($ledger) {
                    $account_id = $ledger->account_id;
                }

                Ledger::updateOrCreate([
                    'company_id' => company_id(),
                    'ledgerable_type' => get_class($item),
                    'ledgerable_id' => $item->id,
                    'account_id' => $account_id,
                ], [
                    'issued_at' => $bill->issued_at,
                    'entry_type' => 'item',
                    'debit' => $item->total,
                ]);
            });

            $bill->item_taxes()->each(function ($item_tax) use ($bill) {
                $account_id = AccountTax::where('tax_id', $item_tax->tax_id)->pluck('account_id')->first();

                $ledger = Ledger::where('ledgerable_type', get_class($item_tax))->where('ledgerable_id', $item_tax->id)->first();

                if ($ledger) {
                    $account_id = $ledger->account_id;
                }

                Ledger::updateOrCreate([
                    'company_id' => company_id(),
                    'ledgerable_type' => get_class($item_tax),
                    'ledgerable_id' => $item_tax->id,
                    'account_id' => $account_id,
                ], [
                    'issued_at' => $bill->issued_at,
                    'entry_type' => 'item',
                    'debit' => $item_tax->amount,
                ]);
            });

            $bill->transactions()->each(function ($transaction) use ($accounts_payable_id) {
                $account_id = AccountBank::where('bank_id', $transaction->account_id)->pluck('account_id')->first();

                if (is_null($account_id)) {
                    $account = $this->createBankAccount($transaction->account);

                    $account_id = $account->id;
                }

                Ledger::updateOrCreate([
                    'company_id' => company_id(),
                    'ledgerable_type' => get_class($transaction),
                    'ledgerable_id' => $transaction->id,
                    'account_id' => $account_id,
                ], [
                    'issued_at' => $transaction->paid_at,
                    'entry_type' => 'total',
                    'credit' => $transaction->amount,
                ]);

                Ledger::updateOrCreate([
                    'company_id' => company_id(),
                    'ledgerable_type' => get_class($transaction),
                    'ledgerable_id' => $transaction->id,
                    'account_id' => $accounts_payable_id,
                ], [
                    'issued_at' => $transaction->paid_at,
                    'entry_type' => 'item',
                    'debit' => $transaction->amount,
                ]);
            });
        });
    }

    /**
     * Copy existing transactions that type's are expense to the ledgers.
     *
     * @return void
     */
    protected function copyExpenseTransactions()
    {
        Transaction::whereNot('reference', 'LIKE', 'journal-entry-ledger%')
            ->type('expense')
            ->isNotDocument()
            ->isNotTransfer()
            ->cursor()
            ->each(function ($transaction) {
                $account_id = AccountBank::where('bank_id', $transaction->account_id)->pluck('account_id')->first();

                if (is_null($account_id)) {
                    $account = $this->createBankAccount($transaction->account);

                    $account_id = $account->id;
                }

                Ledger::updateOrCreate([
                    'company_id' => company_id(),
                    'ledgerable_type' => get_class($transaction),
                    'ledgerable_id' => $transaction->id,
                    'account_id' => $account_id,
                ], [
                    'issued_at' => $transaction->paid_at,
                    'entry_type' => 'total',
                    'credit' => $transaction->amount,
                ]);

                $account_id = Coa::code(setting('double-entry.accounts_expenses', 628))->pluck('id')->first();

                Ledger::updateOrCreate([
                    'company_id' => company_id(),
                    'ledgerable_type' => get_class($transaction),
                    'ledgerable_id' => $transaction->id,
                    'account_id' => $account_id,
                ], [
                    'issued_at' => $transaction->paid_at,
                    'entry_type' => 'item',
                    'debit' => $transaction->amount,
                ]);
            }
        );
    }

    /**
     * Creates a chart of account
     *
     * @param Account $account
     * @return Coa
     */
    protected function createBankAccount(Account $account)
    {
        $chart_of_account = Coa::create([
            'company_id' => company_id(),
            'type_id' => setting('double-entry.types_bank', 6),
            'code' => $this->getNextAccountCode(),
            'name' => $account->name,
            'enabled' => 1,
        ]);

        $chart_of_account->bank()->create([
            'company_id' => company_id(),
            'account_id' => $chart_of_account->id,
            'bank_id' => $account->id,
        ]);

        if ($account->opening_balance <= 0) {
            return $chart_of_account;
        }

        $owner_contribution = Coa::code(setting('double-entry.accounts_owners_contribution'))->first();

        if (is_null($owner_contribution)) {
            return $chart_of_account;
        }

        $request = [
            'company_id' => $account->company_id,
            'paid_at' => $account->created_at,
            'description' => trans('accounts.opening_balance') . ';' . $account->name,
            'reference' => 'opening-balance:' . $chart_of_account->id,
            'items' => [
                ['account_id' => $chart_of_account->id, 'debit' => $account->opening_balance],
                ['account_id' => $owner_contribution->id, 'credit' => $account->opening_balance],
            ],
        ];

        $this->dispatch(new CreateJournalEntry($request));

        return $chart_of_account;
    }
}
