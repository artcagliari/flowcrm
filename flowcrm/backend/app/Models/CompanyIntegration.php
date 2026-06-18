<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyIntegration extends Model
{
    protected $fillable = ['company_id', 'provider', 'credentials', 'is_active'];

    protected $casts = ['credentials' => 'array', 'is_active' => 'boolean'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
