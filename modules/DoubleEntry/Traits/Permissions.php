<?php

namespace Modules\DoubleEntry\Traits;

use App\Models\Banking\Transaction;
use App\Models\Document\Document;
use App\Traits\Modules;
use Modules\CreditDebitNotes\Models\CreditNote;
use Modules\CreditDebitNotes\Models\DebitNote;

trait Permissions
{
    use Modules;

    protected function isNotValidDocumentType($type): bool
    {
        $valid_document_types = [
            Document::INVOICE_TYPE,
            Document::BILL_TYPE,
        ];

        if ($this->moduleIsEnabled('credit-debit-notes')) {
            $valid_document_types[] = CreditNote::TYPE;
            $valid_document_types[] = DebitNote::TYPE;
        }

        return ! in_array($type, $valid_document_types);
    }

    protected function isNotValidTransactionType($type)
    {
        $valid_transaction_types = [
            Transaction::INCOME_TYPE,
            Transaction::EXPENSE_TYPE,
        ];

        if ($this->moduleIsEnabled('credit-debit-notes')) {
            $valid_transaction_types[] ='credit_note_refund';
            $valid_transaction_types[] ='debit_note_refund';
        }

        return ! in_array($type, $valid_transaction_types);
    }
}
