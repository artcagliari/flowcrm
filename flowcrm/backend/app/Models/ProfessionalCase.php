<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfessionalCase extends CompanyModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'professional_cases';

    protected $fillable = [
        'company_id', 'client_id', 'lead_id', 'owner_id', 'profession_mode', 'title', 'status',
        'process_number', 'deadline', 'procedural_status', 'action_type',
        'session_frequency', 'notes', 'opened_at', 'closed_at',
    ];

    protected $casts = [
        'deadline' => 'date',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function client() { return $this->belongsTo(Client::class); }
    public function lead() { return $this->belongsTo(Lead::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }

    public static function createForClient(Client $client, ?Lead $lead = null, array $overrides = []): self
    {
        $company = Company::findOrFail($client->company_id);
        $mode = $company->profession_mode ?? 'empresa';

        $defaults = [
            'company_id' => $client->company_id,
            'client_id' => $client->id,
            'lead_id' => $lead?->id,
            'owner_id' => $client->owner_id ?? $lead?->owner_id,
            'profession_mode' => $mode,
            'title' => 'Conta — '.$client->name,
            'status' => 'ativo',
            'opened_at' => now(),
        ];

        return self::create(array_merge($defaults, $overrides));
    }
}
