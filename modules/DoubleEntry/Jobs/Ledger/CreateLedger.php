<?php

namespace Modules\DoubleEntry\Jobs\Ledger;

use App\Abstracts\Job;
use App\Interfaces\Job\HasOwner;
use App\Interfaces\Job\HasSource;
use App\Interfaces\Job\ShouldCreate;
use Modules\DoubleEntry\Events\Ledger\LedgerCreated;
use Modules\DoubleEntry\Models\Ledger;

class CreateLedger extends Job implements HasOwner, HasSource, ShouldCreate
{
    /**
     * Execute the job.
     *
     * @return \Modules\DoubleEntry\Models\Ledger
     */
    public function handle(): Ledger
    {
        \DB::transaction(function () {
            $this->model = Ledger::create($this->request->all());
        });

        event(new LedgerCreated($this->model));

        return $this->model;
    }
}
