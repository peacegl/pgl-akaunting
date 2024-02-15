<?php

namespace Modules\DoubleEntry\Jobs\Account;

use App\Abstracts\Job;
use App\Events\Common\RelationshipCounting;
use Exception;
use Modules\DoubleEntry\Events\Account\AccountDeleted;
use Modules\DoubleEntry\Traits\Accounts;
use Illuminate\Support\Str;

class DeleteAccount extends Job
{
    use Accounts;

    /**
     * The account instance.
     *
     * @var \Modules\DoubleEntry\Models\Account
     */
    protected $account;

    /**
     * Create a new job instance.
     *
     * @param \Modules\DoubleEntry\Models\Account $account
     * @return void
     */
    public function __construct($account)
    {
        $this->account = $account;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        $this->authorize();

        \DB::transaction(function () {
            $this->account->delete();
        });

        event(new AccountDeleted($this->account));

        return true;
    }

    /**
     * Determine if this action is applicable.
     *
     * @return void
     * 
     * @throws \Exception
     */
    public function authorize()
    {
        $relationships = $this->countRelationships($this->account, [
            'bank' => 'bank_accounts',
            'tax' => 'tax_rates',
            'ledgers' => 'ledgers',
        ]);

        $settings = $this->countSettings($this->account);

        if (!empty($relationships) || !empty($settings)) {

            $message = trans('messages.warning.deleted', ['name' => trans($this->account->name), 'text' => implode(', ', $relationships)]);

            throw new \Exception($message);
        }
    }

    public function countRelationships($model, $relationships): array
    {
        $record = new \stdClass();
        $record->model = $model;
        $record->relationships = $relationships;

        event(new RelationshipCounting($record));

        $counter = [];

        foreach ((array) $record->relationships as $relationship => $text) {
            if (!$c = $model->$relationship()->count()) {
                continue;
            }

            $text = 'double-entry::general.' . $text;
            $counter[] = (($c > 1) ? $c . ' ' : null ) . strtolower(trans_choice($text, ($c > 1) ? 2 : 1));
        }

        return $counter;
    }
}
