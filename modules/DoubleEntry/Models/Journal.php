<?php

namespace Modules\DoubleEntry\Models;

use App\Abstracts\Model;
use App\Traits\Currencies;
use App\Traits\Documents;
use App\Traits\Media;
use Bkwld\Cloner\Cloneable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\DoubleEntry\Database\Factories\Journal as JournalFactory;
use Modules\DoubleEntry\Traits\Journal as JournalTraits;

class Journal extends Model
{
    use Cloneable, Currencies, Documents, HasFactory, Media, JournalTraits;

    public const BASIS = [
        'cash' => 'general.cash',
        'accrual' => 'general.accrual',
    ];

    protected $table = 'double_entry_journals';

    protected $fillable = ['company_id', 'paid_at', 'amount', 'description', 'reference', 'journal_number', 'basis', 'currency_code', 'currency_rate', 'created_from', 'created_by'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'double',
    ];

    /**
     * Sortable columns.
     *
     * @var array
     */
    public $sortable = ['paid_at', 'amount'];

    /**
     * @var array
     */
    public $cloneable_relations = ['ledgers'];

    public function ledger()
    {
        return $this->morphOne('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
    }

    public function ledgers()
    {
        return $this->morphMany('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
    }

    /**
     * Get the current balance.
     *
     * @return string
     */
    public function getAttachmentAttribute($value)
    {
        if (!empty($value) && !$this->hasMedia('attachment')) {
            return $value;
        } elseif (!$this->hasMedia('attachment')) {
            return false;
        }

        return $this->getMedia('attachment')->all();
    }

    public function getNumberAttribute()
    {
        return $this->journal_number;
    }

    /**
     * Get the line actions.
     *
     * @return array
     */
    public function getLineActionsAttribute()
    {
        $actions = [];

        $actions[] = [
            'title' => trans('general.show'),
            'icon' => 'visibility',
            'url' => route('double-entry.journal-entry.show', $this->id),
            'permission' => 'read-double-entry-journal-entry',
        ];

        $actions[] = [
            'title' => trans('general.edit'),
            'icon' => 'edit',
            'url' => route('double-entry.journal-entry.edit', $this->id),
            'permission' => 'update-double-entry-journal-entry',
        ];

        $actions[] = [
            'title' => trans('general.duplicate'),
            'icon' => 'file_copy',
            'url' => route('double-entry.journal-entry.duplicate', $this->id),
            'permission' => 'create-double-entry-journal-entry',
        ];

        $actions[] = [
            'type' => 'delete',
            'title' => trans_choice('double-entry::general.journals', 1),
            'icon' => 'delete',
            'route' => 'double-entry.journal-entry.destroy',
            'permission' => 'delete-double-entry-journal-entry',
            'model' => $this,
        ];

        return $actions;
    }

    /**
     * @inheritDoc
     *
     * @param  Journal $journal
     * @param  boolean $child
     */
    public function onCloning($journal, $child = null)
    {
        $this->journal_number = $this->getNextJournalNumber('double-entry.journal');
    }

    /**
     * @inheritDoc
     *
     * @param  Journal $journal
     */
    public function onCloned($journal)
    {
        $this->increaseNextDocumentNumber('double-entry.journal');
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return JournalFactory::new();
    }
}
