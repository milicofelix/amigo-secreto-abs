<?php

namespace Tests\Concerns;

use App\Models\AmigoSecreto\Group;
use App\Models\User;

trait HasOrganizerGroups
{
    private function signInOrganizer(): User
    {
        $organizer = User::factory()->organizer()->create();
        $this->actingAs($organizer);

        return $organizer;
    }

    private function ownedGroup(array $attributes): Group
    {
        $group = new Group($attributes);
        $group->user()->associate(auth()->user());
        $group->save();

        return $group;
    }
}
