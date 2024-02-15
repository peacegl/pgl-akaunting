<?php

namespace Modules\DoubleEntry\Http\Middleware;

use Closure;
use OutOfBoundsException;
use Illuminate\Support\Str;
use InvalidArgumentException;
use UnexpectedValueException;;

class Money
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->method() == 'POST' || $request->method() == 'PATCH') {
            $amount = $request->get('amount');
            $currency_code = $request->get('currency_code');
            $items = $request->get('items');

            if (empty($currency_code)) {
                $currency_code = setting('default.currency');
            }

            if (!empty($amount)) {
                $amount = $this->getAmount($this->getMoneyFormat($request->get('amount')), $currency_code);

                $request->request->set('amount', $amount);
            }

            if (!empty($items)) {
                foreach ($items as $key => $item) {
                    $debit = $credit = 0;

                    if (isset($item['debit'])) {
                        $debit = $item['debit'];
                    }

                    if (isset($item['credit'])) {
                        $credit = $item['credit'];
                    }

                    $items[$key]['debit'] = $this->getAmount($this->getMoneyFormat($debit), $currency_code);
                    $items[$key]['credit'] = $this->getAmount($this->getMoneyFormat($credit), $currency_code);
                }

                $request->request->set('items', $items);
            }
        }

        return $next($request);
    }

    protected function getMoneyFormat($parameter)
    {
        $money_format = Str::replace(',', '.', $parameter);

        if ($dot_count = Str::substrCount($money_format, '.') > 1) {
            if ($dot_count > 2) {
                $money_format = Str::replaceLast('.', '#', $money_format);
                $money_format = Str::replace('.', '', $money_format);
                $money_format = Str::replaceFirst('#', '.', $money_format);
            } else {
                $money_format = Str::replaceFirst('.', '', $money_format);
            }
        }

        return (double) $money_format;
    }

    protected function getAmount($money_format, $currency_code)
    {
        try {
            if (config('money.currencies.' . $currency_code . '.decimal_mark') !== '.') {
                $money_format = Str::replaceFirst('.', config('money.currencies.' . $currency_code . '.decimal_mark'), $money_format);
            }

            $amount = money($money_format, $currency_code, false)->getAmount();
        } catch (InvalidArgumentException | OutOfBoundsException | UnexpectedValueException $e) {
            report($e);

            $amount = 0;

            if ($money_format === null) {
                $amount = $money_format;
            }
        }

        return $amount;
    }
}
