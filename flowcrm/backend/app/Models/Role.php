<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends CompanyModel
{
    use HasFactory;

    protected $fillable = ['company_id', 'name'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
