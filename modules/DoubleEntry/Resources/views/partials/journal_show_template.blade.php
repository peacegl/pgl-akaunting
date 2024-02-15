<div class="print-template">
    @if (! $hideCompany)
        <table class="border-bottom-1">
            <tr>
                @if (! $hideCompanyLogo)
                <td style="width:20%; padding: 0 0 15px 0;" valign="top">
                    @if (!empty($transaction->contact->logo) && !empty($transaction->contact->logo->id))
                        <img src="{{ Storage::url($transaction->contact->logo->id) }}" height="70" width="70" alt="{{ $transaction->contact_name }}" />
                    @else
                        <img src="{{ $logo }}" height="70" width="70" alt="{{ setting('company.name') }}" />
                    @endif
                </td>
                @endif

                @if (! $hideCompanyDetails)
                <td class="text" style="width: 80%; padding: 0 0 15px 0;">
                    @if (! $hideCompanyName)
                        <span class="font-semibold text">
                            {{ setting('company.name') }}
                        </span>
                    @endif

                    @if (! $hideCompanyAddress)
                        <p>{!! (setting('company.address')) !!}</p>
                    @endif

                    @if (! $hideCompanyTaxNumber)
                        @if (setting('company.tax_number'))
                            <p>
                                {{ trans('general.tax_number') }}: {{ setting('company.tax_number') }}
                            </p>
                        @endif
                    @endif

                    @if (! $hideCompanyPhone)
                        @if (setting('company.phone'))
                            <p>
                                {{ setting('company.phone') }}
                            </p>
                        @endif
                    @endif

                    @if (! $hideCompanyEmail)
                            <p>{{ setting('company.email') }}</p>
                        @endif
                </td>
                @endif
            </tr>
        </table>
    @endif

    @if (! $hideContentTitle)
        <table>
            <tr>
                <td style="width: 60%; padding: 15px 0 15px 0;">
                    <div class="font-semibold" style="font-size: 12px;">
                        {{ $textContentTitle != trans_choice($textContentTitle, 1) ? trans_choice($textContentTitle, 1) : trans($textContentTitle) }}
                    </div>
                </td>
            </tr>
        </table>
    @endif

    <table>
        @stack('journal_number_input_start')
            @if (! $hideNumber)
                <tr>
                    <td valign="top" class="font-semibold" style="width: 30%; margin: 0px; padding: 8px 4px 0 0; font-size: 12px;">
                        {{ trans_choice($textNumber, 1) }}
                    </td>

                    <td valign="top" class="border-bottom-dashed-black" style="width:70%; margin: 0px; padding: 8px 0 0 0; font-size: 12px;">
                        {{ $transaction->number }}
                    </td>
                </tr>
            @endif
        @stack('journal_number_input_end')

        @stack('paid_at_input_start')
            @if (! $hidePaidAt)
                <tr>
                    <td valign="top" class="font-semibold" style="width: 30%; margin: 0px; padding: 8px 4px 0 0; font-size: 12px;">
                        {{ trans($textPaidAt) }}
                    </td>

                    <td valign="top" class="border-bottom-dashed-black" style="width:70%; margin: 0px; padding: 8px 0 0 0; font-size: 12px;">
                        <x-date date="{{ $transaction->paid_at }}" />
                    </td>
                </tr>
            @endif
        @stack('paid_at_input_end')

        @stack('reference_input_start')
            @if (! $hideReference)
                <tr>
                    <td valign="top" class="font-semibold" style="width: 30%; margin: 0px; padding: 8px 4px 0 0; font-size: 12px;">
                        {{ trans($textReference) }}
                    </td>

                    <td valign="top" class="border-bottom-dashed-black" style="width:70%; margin: 0px; padding: 8px 0 0 0; font-size: 12px;">
                        {{ $transaction->reference }}
                    </td>
                </tr>
            @endif
        @stack('reference_input_end')

        @stack('description_input_start')
            @if (! $hideDescription)
                <tr>
                    <td valign="top" class="font-semibold" style="width: 30%; margin: 0px; padding: 8px 4px 0 0; font-size: 12px;">
                        {{ trans($textDescription) }}
                    </td>

                    <td valign="top" class="border-bottom-dashed-black" style="width:70%; margin: 0px; padding: 8px 0 0 0; font-size: 12px;">
                        <p style="font-size:12px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; margin: 0;">
                            {!! nl2br($transaction->description) !!}
                        </p>
                    </td>
                </tr>
            @endif
        @stack('description_input_end')
    </table>

    <div class="row">
        <div class="col-100">
            <div class="text extra-spacing">
                <table class="lines lines-radius-border">
                    <thead style="background-color:#55588b !important; -webkit-print-color-adjust: exact;">
                        <tr>
                            <td class="item text font-semibold text-alignment-left text-left text-white">
                                <span>
                                    {{ trans_choice('general.accounts', 1) }}
                                </span>
                            </td>

                            <td class="price text font-semibold text-alignment-right text-right text-white">
                                <span>
                                    {{ trans_choice('double-entry::general.debits', 2) }}
                                </span>
                            </td>

                            <td class="total text font-semibold text-white text-alignment-right text-right">
                                <span>
                                    {{ trans_choice('double-entry::general.credits', 2) }}
                                </span>
                            </td>
                        </tr>
                    </thead>

                    <tbody>
                        @if ($transaction->ledgers->count())
                            @foreach($transaction->ledgers as $ledger)
                                <tr>
                                    <td class="item text text-alignment-left text-left">
                                        {{ $ledger->account->trans_name }}
                                    </td>

                                    <td class="price text text-alignment-right text-right">
                                        @if ($ledger->debit) 
                                            <x-money :amount="$ledger->debit" :currency="$transaction->currency_code" convert />
                                        @endif
                                    </td>

                                    <td class="total text text-alignment-right text-right">
                                        @if ($ledger->credit) 
                                            <x-money :amount="$ledger->credit" :currency="$transaction->currency_code" convert />
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text text-center empty-items">
                                    {{ trans('documents.empty_items') }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if (! $hideAmount)
        <div class="row mt-9 clearfix">
            <div class="col-40 float-right text-right">
                <div class="text border-bottom-1 py-1">
                    <span class="float-left font-semibold">
                        {{ trans($textAmount) }}
                    </span>

                    <span>
                        <x-money :amount="$transaction->amount" :currency="$transaction->currency_code" convert />
                    </span>
                </div>
            </div>
        </div>
    @endif
</div>