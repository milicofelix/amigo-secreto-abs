<?php

namespace App\Policies\AmigoSecreto;

use App\Models\AmigoSecreto\Group;
use App\Models\User;

class GroupPolicy
{
    public function manage(User $user, Group $group): bool
    {
        return $user->is_organizer && $group->user_id === $user->id;
    }
}
