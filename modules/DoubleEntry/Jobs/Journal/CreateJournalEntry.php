<?php

namespace Modules\DoubleEntry\Jobs\Journal;

use App\Abstracts\Job;
use App\Interfaces\Job\HasOwner;
use App\Interfaces\Job\HasSource;
use App\Interfaces\Job\ShouldCreate;
use Modules\DoubleEntry\Events\Journal\JournalCreated;
use Modules\DoubleEntry\Models\Journal;

class CreateJournalEntry extends Job implements HasOwner, HasSource, ShouldCreate
{
    /**
     * Execute the job.
     *
     * @return \Modules\DoubleEntry\Models\Journal
     */
    public function handle(): Journal
    {
        \DB::transaction(function () {
            $input = $this->request->input();
            $amount = 0;
            $input['amount'] = $amount;

            $this->model = Journal::create($input);

            foreach ($input['items'] as $item) {
                if (!empty($item['debit'])) {
                    $this->model->ledger()->create([
                        'company_id' => $this->model->company_id,
                        'account_id' => $item['account_id'],
                        'issued_at' => $this->model->paid_at,
                        'entry_type' => 'item',
                        'debit' => $item['debit'],
                        'notes' => $item['notes'] ?? null,
                    ]);

                    $amount += $item['debit'];
                } else {
                    $this->model->ledger()->create([
                        'company_id' => $this->model->company_id,
                        'account_id' => $item['account_id'],
                        'issued_at' => $this->model->paid_at,
                        'entry_type' => 'item',
                        'credit' => $item['credit'],
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            $this->model->amount = $amount;
            $this->model->save();

            // Upload attachment
            if ($this->request->file('attachment')) {
                foreach ($this->request->file('attachment') as $attachment) {
                    $media = $this->getMedia($attachment, 'journal_entries');

                    $this->model->attachMedia($media, 'attachment');
                }
            }
        });

        event(new JournalCreated($this->model));

        return $this->model;
    }
}
