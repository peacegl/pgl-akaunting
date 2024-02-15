<?php

namespace Modules\DoubleEntry\Http\Requests;

use App\Abstracts\Http\FormRequest;
use App\Traits\Modules;


class Setting extends FormRequest
{
    use Modules;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $payroll = 'nullable';

        if ($this->moduleIsEnabled('payroll')) {
            $payroll = 'required';
        }

        return [
            'accounts_receivable' => 'required|integer',
            'accounts_payable' => 'required|integer',
            'accounts_sales' => 'required|integer',
            'accounts_expenses' => 'required|integer',
            'accounts_sales_discount' => 'required|integer',
            'accounts_purchase_discount' => 'required|integer',
            'accounts_owners_contribution' => 'required|integer',
            'accounts_payroll' => $payroll . '|integer',
            'types_bank' => 'required|integer',
            'types_tax' => 'required|integer',
            'journal_number_prefix' => 'required',
            'journal_number_digit' => 'required',
            'journal_number_next' => 'required',
        ];
    }
}
