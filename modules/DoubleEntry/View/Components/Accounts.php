<?php

namespace Modules\DoubleEntry\View\Components;

use App\Abstracts\View\Component;
use Modules\DoubleEntry\Models\Account;

class Accounts extends Component
{
    public $name;

    public $label;

    public $options;

    public $selected;

    public $required;

    public $group;

    public $formGroupClass;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string $name = 'de_account_id', string $label = '', $options = [], $selected = null, bool $required = false, bool $group = true, string $formGroupClass = '')
    {
        $this->name = $name;
        $this->label = $label;
        $this->options = $options;
        $this->selected = $selected;
        $this->required = $required;
        $this->group = $group;
        $this->formGroupClass = $formGroupClass;

        if (empty($this->formGroupClass)) {
            $this->formGroupClass = 'sm:col-span-3';
        }

        if (empty($this->label)) {
            $this->label = trans_choice('double-entry::general.chart_of_accounts', 1);
        }

        if (empty($this->options)) {
            Account::with('type')
                ->enabled()
                ->orderBy('code')
                ->get()
                ->each(function ($account) use (&$options) {
                    $options[trans($account->type->name)][$account->id] = $account->code . ' - ' . $account->trans_name;
                });

            ksort($options);

            $this->options = $options;
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('double-entry::components.accounts');
    }
}
