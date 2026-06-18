<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowUpSequence extends CompanyModel
{
    use HasFactory;

    protected $fillable = ['company_id', 'name', 'trigger_type', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function steps()
    {
        return $this->hasMany(FollowUpSequenceStep::class, 'sequence_id')->orderBy('position');
    }

    public function enrollments()
    {
        return $this->hasMany(SequenceEnrollment::class, 'sequence_id');
    }
}
