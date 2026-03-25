<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'salary_payments';
    protected $fillable = ['company_id', 'worker_id', 'paid_by_id', 'amount', 'note'];

    public function worker() { return $this->belongsTo(User::class, 'worker_id'); }
    public function paidBy() { return $this->belongsTo(User::class, 'paid_by_id'); }

    public function toApiArray(): array
    {
        return [
            'id'          => $this->id,
            'type'        => 'payment',
            'worker_id'   => $this->worker_id,
            'worker_name' => $this->worker?->name,
            'amount'      => (float) $this->amount,
            'note'        => $this->note,
            'paid_by'     => $this->paidBy?->name,
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
