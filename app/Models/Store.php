<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'company_name',
        'tax_number',
        'email',
        'phone',
        'street',
        'city',
        'state',
        'postal_code',
        'country',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function cashRegisters(): HasMany
    {
        return $this->hasMany(CashRegister::class);
    }
}
