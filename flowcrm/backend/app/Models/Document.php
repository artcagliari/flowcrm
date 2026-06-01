<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'user_id', 'client_id', 'lead_id', 'uploaded_by', 'name', 'original_name', 'category', 'path', 'mime_type', 'size', 'size_bytes', 'description'];

    protected $casts = ['size' => 'integer', 'size_bytes' => 'integer'];
}
