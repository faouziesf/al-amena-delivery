<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'shop_name', 'fiscal_number', 'business_sector',
        'identity_document', 'offer_delivery_price', 'offer_return_price'
    ];

    protected $casts = [
        'offer_delivery_price' => 'decimal:3',
        'offer_return_price' => 'decimal:3',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function hasCompletedProfile()
    {
        return !empty($this->shop_name) && 
               !empty($this->fiscal_number) && 
               !empty($this->business_sector);
    }

    public function getDeliveryPriceAttribute()
    {
        return $this->offer_delivery_price;
    }

    public function getReturnPriceAttribute()
    {
        return $this->offer_return_price;
    }
}