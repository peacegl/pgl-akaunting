<?php

namespace Modules\DoubleEntry\BulkActions;

use App\Abstracts\BulkAction;
use Modules\DoubleEntry\Jobs\Account\DeleteAccount;
use Modules\DoubleEntry\Jobs\Account\UpdateAccount;
use Modules\DoubleEntry\Models\Account;

class ChartOfAccounts extends BulkAction
{
    public $model = Account::class;

    public $text = 'double-entry::general.chart_of_accounts';

    public $path = [
        'group' => 'double-entry',
        'type' => 'chart-of-accounts',
    ];

    public $actions = [
        'enable' => [
            'icon' => 'check_circle',
            'name' => 'general.enable',
            'message' => 'bulk_actions.message.enable',
            'path' => ['group' => 'double-entry', 'type' => 'chart-of-accounts'],
            'type' => '*',
            'permission' => 'update-double-entry-chart-of-accounts',
        ],
        'disable' => [
            'name' => 'general.disable',
            'message' => 'bulk_actions.message.disable',
            'path' =>  ['group' => 'double-entry', 'type' => 'chart-of-accounts'],
            'type' => '*',
            'permission' => 'update-double-entry-chart-of-accounts',
        ],
        'delete' => [
            'icon' => 'delete',
            'name' => 'general.delete',
            'message' => 'bulk_actions.message.delete',
            'path' =>  ['group' => 'double-entry', 'type' => 'chart-of-accounts'],
            'type' => '*',
            'permission' => 'delete-double-entry-chart-of-accounts',
        ],
    ];

    public function enable($request)
    {
        $accounts = $this->getSelectedRecords($request);

        foreach ($accounts as $account) {
            try {
                $this->dispatch(new UpdateAccount($account, ['enabled' => 1]));
            } catch (\Exception $e) {
                flash($e->getMessage())->error()->important();
            }
        }
    }

    public function disable($request)
    {
        $accounts = $this->getSelectedRecords($request);

        foreach ($accounts as $account) {
            try {
                $this->dispatch(new UpdateAccount($account, ['enabled' => 0]));
            } catch (\Exception $e) {
                flash($e->getMessage())->error()->important();
            }
        }
    }

    public function destroy($request)
    {
        $accounts = $this->getSelectedRecords($request);

        foreach ($accounts as $account) {
            try {
                $this->dispatch(new DeleteAccount($account));
            } catch (\Exception $e) {
                flash($e->getMessage())->error()->important();
            }
        }
    }
}
