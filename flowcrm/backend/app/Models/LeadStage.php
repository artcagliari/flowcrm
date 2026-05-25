<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadStage extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'name', 'position'];

    protected $casts = ['position' => 'integer'];

    public function leads() { return $this->hasMany(Lead::class); }
}
