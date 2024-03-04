<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\Journal;
use Modules\DoubleEntry\Models\Ledger;
use Modules\DoubleEntry\Traits\Journal as Traits;
use Modules\DoubleEntry\Events\Journal\JournalCreated;
use Modules\DoubleEntry\Jobs\Ledger\CreateLedger;
use App\Utilities\Date;

class AccountBalances extends Command 
{
    use Traits;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculating balances sum for accounts';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        company(1)->makeCurrent();
        $pglAccount = $this->getAccountByCode('8802');
        if ($pglAccount) {

            $results = \DB::select('SELECT 
                    total_invoice_amount,
                    COALESCE(total_invoice_amount, 0) - COALESCE(total_discount, 0) - COALESCE(total_payment_received, 0) AS remaining_amount
                FROM (
                    SELECT 
                        SUM(invoice_amount) AS total_invoice_amount,
                        SUM(DISCOUNT) AS total_discount,
                        SUM(payment_received) AS total_payment_received
                    FROM (
                        SELECT 
                            INVOICES.ID,
                            INVOICES.DISCOUNT,
                            CAST(COALESCE(invoices.payment_received, 0) AS FLOAT) AS payment_received,
                            CAST(
                                SUM(
                                    COALESCE(vehicle_costs.towing_cost, 0) +
                                    COALESCE(vehicle_costs.dismantal_cost, 0) +
                                    COALESCE(vehicle_costs.ship_cost, 0) +
                                    COALESCE(vehicle_costs.storage_pod_cost, 0) +
                                    CASE WHEN invoices.title_charge_visible = TRUE THEN COALESCE(vehicle_costs.storage_pod_cost, 0) ELSE 0 END +
                                    COALESCE(vehicle_costs.other_cost, 0)
                                ) AS FLOAT
                            ) AS invoice_amount
                        FROM 
                            invoices 
                        JOIN 
                            CONTAINERS ON INVOICES.CONTAINER_ID = CONTAINERS.ID
                        LEFT JOIN 
                            VEHICLES ON VEHICLES.CONTAINER_ID = CONTAINERS.ID
                        LEFT JOIN 
                            VEHICLE_COSTS ON VEHICLE_COSTS.VEHICLE_ID = VEHICLES.ID
                        LEFT JOIN 
                            COMPANIES ON INVOICES.COMPANY_ID = COMPANIES.ID
                        LEFT JOIN 
                            MIX_SHIPPING_INVOICES ON MIX_SHIPPING_INVOICES.CONTAINER_ID = CONTAINERS.ID
                        LEFT JOIN 
                            USERS ON INVOICES.CREATED_BY = USERS.ID
                        WHERE 
                            INVOICES.STATUS = \'open\'
                            AND INVOICES.DELETED_AT IS NULL
                            AND MIX_SHIPPING_INVOICES.CONTAINER_ID IS NULL 
                        GROUP BY 
                            invoices.id, invoices.invoice_number, containers.container_number, invoices.invoice_number, 
                            invoices.status, invoices.updated_at, invoice_due_date, invoice_date, invoices.id, received_date
                    ) AS subquery
                ) AS totals;
            ');

            $pglAccountJournelEntry = $this->addJournelEntry($results[0]->total_invoice_amount);                                       
            $this->addLedgerEntry($pglAccountJournelEntry, $pglAccount);          
                // \Illuminate\Support\Facades\Log::info("results: ", [$results[0]->total_invoice_amount]);
        }

        $mixAccount =$this->getAccountByCode('8803');

        if ($mixAccount) {

            $mixResults = \DB::select('SELECT 
                    total_invoice_amount,
                    COALESCE(total_invoice_amount, 0) - COALESCE(total_discount, 0) - COALESCE(total_payment_received, 0) AS remaining_amount
                FROM (
                    SELECT 
                        SUM(invoice_amount) AS total_invoice_amount,
                        SUM(DISCOUNT) AS total_discount,
                        SUM(payment_received) AS total_payment_received
                    FROM (
                        SELECT 
                            INVOICES.ID,
                            INVOICES.DISCOUNT,
                            CAST(COALESCE(invoices.payment_received, 0) AS FLOAT) AS payment_received,
                            CAST(
                                SUM(
                                    COALESCE(vehicle_costs.towing_cost, 0) +
                                    COALESCE(vehicle_costs.dismantal_cost, 0) +
                                    COALESCE(vehicle_costs.ship_cost, 0) +
                                    COALESCE(vehicle_costs.storage_pod_cost, 0) +
                                    CASE WHEN invoices.title_charge_visible = TRUE THEN COALESCE(vehicle_costs.storage_pod_cost, 0) ELSE 0 END +
                                    COALESCE(vehicle_costs.other_cost, 0)
                                ) AS FLOAT
                            ) AS invoice_amount
                        FROM 
                            invoices 
                        JOIN 
                            CONTAINERS ON INVOICES.CONTAINER_ID = CONTAINERS.ID
                        LEFT JOIN 
                            VEHICLES ON VEHICLES.CONTAINER_ID = CONTAINERS.ID
                        LEFT JOIN 
                            VEHICLE_COSTS ON VEHICLE_COSTS.VEHICLE_ID = VEHICLES.ID
                        LEFT JOIN 
                            COMPANIES ON INVOICES.COMPANY_ID = COMPANIES.ID
                        LEFT JOIN 
                            MIX_SHIPPING_INVOICES ON MIX_SHIPPING_INVOICES.CONTAINER_ID = CONTAINERS.ID
                        LEFT JOIN 
                            USERS ON INVOICES.CREATED_BY = USERS.ID
                        WHERE 
                            INVOICES.STATUS = \'open\'
                            AND INVOICES.DELETED_AT IS NULL
                            AND MIX_SHIPPING_INVOICES.CONTAINER_ID IS NULL 
                        GROUP BY 
                            invoices.id, invoices.invoice_number, containers.container_number, invoices.invoice_number, 
                            invoices.status, invoices.updated_at, invoice_due_date, invoice_date, invoices.id, received_date
                    ) AS subquery
                ) AS totals;
            ');

            $mixAccountJournelEntry = $this->addJournelEntry($mixResults[0]->total_invoice_amount, 'Automatic Mix-Invoice');
            $this->addLedgerEntry($mixAccountJournelEntry, $mixAccount, 'Automatic Mix-Invoice');
        }
    }

    protected function getAccountByCode($code)
    {
        return Account::where('code', $code)
                    ->where('company_id', 1)
                    ->first();
    }

    protected function getJournelByRef($ref)
    {
        return Journal::where('company_id', '1')
        ->where('reference', $ref)
        ->first();
    }

    protected function getLedgerByRef($ref)
    {
        return Ledger::where('company_id', '1')
        ->where('reference', $ref)
        ->first();
    }

    protected function addJournelEntry($amount, $ref = 'Automatic Open-Invoice')
    {
        $journal = $this->getJournelByRef($ref);
        
        if (! $journal) {
            $journal_number = $this->getNextJournalNumber();
            $journal = Journal::create([
                'company_id'    => '1',
                'reference'     => $ref,
                'journal_number'=> $journal_number,
                'amount'        => $amount,
                'currency_code' => 'USD',
                'currency_rate' => 1,
                'paid_at'       => Date::now()->toDateString(),
                'description'   => 'Entry from pgl crm system',
                'basis'         => 'Accrual',
            ]);

            event(new JournalCreated($journal));
        } else {
            $journal->amount = $amount;
            $journal->save();
        }
        
        return $journal;
    }

    protected function addLedgerEntry($journal, $account, $ref = 'Automatic Open-Invoice')
    {
        $ledger = $this->getLedgerByRef($ref);
        
        if (! $ledger) {
            $ledger = Ledger::create([
                'company_id'        => '1',
                'ledgerable_id'     => $journal->id,
                'ledgerable_type'   => 'Modules\DoubleEntry\Models\Journal',
                'entry_type'        => 'item',
                'issued_at'         =>  Date::now()->format('Y-m-d'),
                'reference'         => 'Automatic Mix-Invoice',
                'account_id'        => $account->id,
                'debit'              => $journal->amount,
            ]);
            event(new CreateLedger($ledger));
        } else {
            $ledger->debit = $journal->amount;
            $ledger->save();
        }
        
        return $ledger;
    }
}