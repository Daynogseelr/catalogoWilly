<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_sucursal','id_seller', 'id_client', 'id_currency_official', 'id_currency_bill', 'id_closure', 'id_closureI',
        'code', 'rate_bill', 'rate_official', 'abbr_bill', 'abbr_official', 'abbr_principal',
        'discount_percent', 'total_amount', 'discount', 'net_amount',
        'type', 'status', 'payment', 'creditDays'
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'id_seller');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    public function currencyOfficial()
    {
        return $this->belongsTo(Currency::class, 'id_currency_official');
    }

    public function currencyBill()
    {
        return $this->belongsTo(Currency::class, 'id_currency_bill');
    }

    public function bill_payments()
    {
        return $this->hasMany(Bill_payment::class, 'id_bill');
    }
     public function bill_details()
    {
        return $this->hasMany(Bill_detail::class, 'id_bill');
    }
    public function getDueDateAttribute()
    {
        if ($this->creditDays !== null && $this->creditDays > 0) {
            return $this->created_at->addDays($this->creditDays);
        }
        return null;
    }

    // Accesor para calcular el monto pagado
    public function getAmountPaidAttribute()
    {
        // Monto pagado = Monto neto total - Saldo pendiente
        return ($this->net_amount ?? 0) - ($this->payment ?? 0);
    }

    // Accesor para calcular el estado
    public function getCalculatedStatusAttribute()
    {
        $outstanding = $this->payment ?? 0;
        $dueDate = $this->due_date; // Using the accessor

        if ($outstanding <= 0.01) { // Consider small floating point differences for 'Paid'
            return 'Pagada';
        } elseif ($dueDate && $dueDate->isPast()) {
            return 'Vencida';
        } elseif ($outstanding > 0 && $outstanding < ($this->net_amount ?? 0)) {
            return 'Parcialmente Pagada';
        } else {
            return 'Pendiente'; // Implica que outstanding == net_amount
        }
    }
    
}
