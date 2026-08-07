<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'trade_name',
        'tax_number',
        'phone',
        'email',
        'website',
        'address',
        'city',
        'postal_code',
        'country',
        'currency',
        'timezone',
        'logo',
        'receipt_footer',
        'printer_name',
        'vat',
        'is_open',
    ];

    protected $casts = [
        'vat' => 'decimal:2',
        'is_open' => 'boolean',
    ];

    protected $appends = [
        'logo_url',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        return Storage::disk('public')->url($this->logo);
    }
}
