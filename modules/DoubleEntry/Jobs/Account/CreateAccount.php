<?php

namespace Modules\DoubleEntry\Jobs\Account;

use App\Abstracts\Job;
use App\Interfaces\Job\HasOwner;
use App\Interfaces\Job\HasSource;
use App\Interfaces\Job\ShouldCreate;
use Modules\DoubleEntry\Events\Account\AccountCreated;
use Modules\DoubleEntry\Models\Account;

class CreateAccount extends Job implements HasOwner, HasSource, ShouldCreate
{
    /**
     * Execute the job.
     *
     * @return Account
     */
    public function handle(): Account
    {
        \DB::transaction(function () {
            $this->model = Account::create($this->request->all());
        });

        event(new AccountCreated($this->model));

        return $this->model;
    }
}
