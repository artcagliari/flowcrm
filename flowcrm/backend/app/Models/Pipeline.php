<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pipeline extends CompanyModel
{
    use HasFactory;

    protected $fillable = ['company_id', 'name', 'is_default'];

    protected $casts = ['is_default' => 'boolean'];

    public function stages()
    {
        return $this->hasMany(LeadStage::class)->orderBy('position');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }
}
