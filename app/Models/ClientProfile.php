<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'shop_name', 'fiscal_number', 'business_sector',
        'identity_document', 'offer_delivery_price', 'offer_return_price',
        'internal_notes', 'validation_status', 'validated_by', 'validated_at', 'validation_notes'
    ];

    protected $casts = [
        'offer_delivery_price' => 'decimal:3',
        'offer_return_price' => 'decimal:3',
        'validated_at' => 'datetime',
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

    // Accessors pour compatibilité avec la vue - CORRECTION IMPORTANTE
    public function getDeliveryPriceAttribute()
    {
        return $this->offer_delivery_price;
    }

    public function getReturnPriceAttribute()
    {
        return $this->offer_return_price;
    }

    // Accessors pour formatage
    public function getFormattedDeliveryPriceAttribute()
    {
        return number_format($this->offer_delivery_price ?? 0, 3);
    }

    public function getFormattedReturnPriceAttribute()
    {
        return number_format($this->offer_return_price ?? 0, 3);
    }

    // Validation du matricule fiscal tunisien
    public function isValidFiscalNumber()
    {
        if (empty($this->fiscal_number)) {
            return true; // Optionnel
        }
        
        // Format: 7 chiffres + 3 lettres + 3 chiffres
        return preg_match('/^[0-9]{7}[A-Z]{3}[0-9]{3}$/', $this->fiscal_number);
    }

    // Scopes
    public function scopeWithShop($query)
    {
        return $query->whereNotNull('shop_name');
    }

    public function scopeByBusinessSector($query, $sector)
    {
        return $query->where('business_sector', 'like', "%{$sector}%");
    }

    public function scopeCompletedProfiles($query)
    {
        return $query->whereNotNull('shop_name')
                    ->whereNotNull('fiscal_number')
                    ->whereNotNull('business_sector');
    }

    // Méthodes business
    public function getBusinessDisplayName()
    {
        if ($this->shop_name) {
            return $this->shop_name;
        }
        
        if ($this->business_sector) {
            return 'Entreprise - ' . $this->business_sector;
        }
        
        return 'Professionnel';
    }

    public function hasBusinessInfo()
    {
        return !empty($this->shop_name) || 
               !empty($this->business_sector) || 
               !empty($this->fiscal_number);
    }

    public function getCompletionPercentage()
    {
        $fields = ['shop_name', 'fiscal_number', 'business_sector', 'identity_document'];
        $completed = 0;
        
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $completed++;
            }
        }
        
        return round(($completed / count($fields)) * 100);
    }

    // Validation des prix
    public function hasValidPricing()
    {
        return $this->offer_delivery_price > 0 && $this->offer_return_price > 0;
    }

    public function getPricingDifference()
    {
        if (!$this->hasValidPricing()) {
            return 0;
        }
        
        return $this->offer_delivery_price - $this->offer_return_price;
    }

    // Formatage pour affichage
    public function toDisplayArray()
    {
        return [
            'shop_name' => $this->shop_name,
            'fiscal_number' => $this->fiscal_number,
            'business_sector' => $this->business_sector,
            'identity_document' => $this->identity_document,
            'delivery_price' => $this->getFormattedDeliveryPriceAttribute(),
            'return_price' => $this->getFormattedReturnPriceAttribute(),
            'completion_percentage' => $this->getCompletionPercentage(),
            'has_business_info' => $this->hasBusinessInfo(),
            'valid_pricing' => $this->hasValidPricing(),
        ];
    }
}