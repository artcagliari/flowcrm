<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CompanyRecordPolicy
{
    public function view(User $user, Model $record): bool
    {
        return $user->companies()->whereKey($record->company_id)->exists();
    }

    public function update(User $user, Model $record): bool
    {
        return $this->view($user, $record);
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->view($user, $record);
    }
}
