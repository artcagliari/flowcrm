<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadStage extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'pipeline_id', 'name', 'position', 'color', 'is_won', 'is_lost'];

    protected $casts = ['position' => 'integer', 'is_won' => 'boolean', 'is_lost' => 'boolean'];

    public function pipeline() { return $this->belongsTo(Pipeline::class); }
    public function leads() { return $this->hasMany(Lead::class); }
    public function deals() { return $this->hasMany(Deal::class); }
}
