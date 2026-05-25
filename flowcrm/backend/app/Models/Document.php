<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'client_id', 'lead_id', 'uploaded_by', 'name', 'category', 'path', 'mime_type', 'size_bytes'];

    protected $casts = ['size_bytes' => 'integer'];
}
