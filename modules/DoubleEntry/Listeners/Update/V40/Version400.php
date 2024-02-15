<?php

namespace Modules\DoubleEntry\Listeners\Update\V40;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use App\Jobs\Common\DeleteDashboard;
use App\Models\Common\Dashboard;
use App\Models\Common\Company;
use App\Models\Module\Module;
use App\Traits\Jobs;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class Version400 extends Listener
{
    use Jobs;

    const ALIAS = 'double-entry';

    const VERSION = '4.0.0';

    /**
     * Handle the event.
     *
     * @param  $event
     * @return void
     */
    public function handle(UpdateFinished $event)
    {
        if ($this->skipThisUpdate($event)) {
            return;
        }

        $this->updateCompanies();

        $this->deleteOldFiles();
    }

    public function updateCompanies()
    {
        Log::channel('stderr')->info('Updating companies...');

        $current_company_id = company_id();

        $company_ids = Module::allCompanies()->alias(static::ALIAS)->pluck('company_id');

        foreach ($company_ids as $company_id) {
            Log::channel('stderr')->info('Updating company: ' . $company_id);

            $company = Company::find($company_id);

            if (! $company instanceof Company) {
                continue;
            }

            $company->makeCurrent();

            $this->deleteDashboards();

            Log::channel('stderr')->info('Company updated: ' . $company_id);
        }

        company($current_company_id)->makeCurrent();

        Log::channel('stderr')->info('Companies updated.');
    }

    protected function deleteDashboards(): void
    {
        Dashboard::where('name', trans('double-entry::general.name'))
            ->get()
            ->each(function ($dashboard) {
                try {
                    $this->dispatch(new DeleteDashboard($dashboard));
                } catch (Throwable $e) {
                    report($e);
                }
            });
    }

    public function deleteOldFiles(): void
    {
        $files = [
            'Listeners/ShowInSettingsPage.php',
            'Database/Seeds/Dashboard.php',
            'Widgets/IncomeByCoa.php',
            'Widgets/LatestIncomeByCoa.php',
            'Widgets/LatestExpensesByCoa.php',
            'Widgets/TotalIncomeByCoa.php',
            'Widgets/TotalExpensesByCoa.php',
            'Widgets/TotalProfitByCoa.php',
            'Resources/views/trial_balance/table.blade.php',
            'Resources/views/trial_balance/table/rows.blade.php',
            'Resources/views/general_ledger/show.blade.php',
            'Resources/views/general_ledger/content.blade.php',
            'Resources/views/journal_report/show.blade.php',
            'Resources/views/journal_report/content.blade.php',
            'Resources/views/balance_sheet/content.blade.php',
            'Resources/views/widgets/income_expense.blade.php',
            'Resources/views/widgets/latest.blade.php',
        ];

        foreach ($files as $file) {
            File::delete(base_path('modules/DoubleEntry/' . $file));
        }
    }
}
