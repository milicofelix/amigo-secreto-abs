<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\HasOrganizerGroups;
use Tests\TestCase;

class AmigoSecretoParticipantTest extends TestCase
{
    use HasOrganizerGroups, RefreshDatabase;

    public function test_group_form_sends_and_saves_a_jpeg_photo(): void
    {
        $this->signInOrganizer();
        Storage::fake('public');
        $group = $this->ownedGroup(['name' => 'Grupo de teste']);
        $store = route('participants.store', $group);

        $this->get(route('groups.edit', $group))
            ->assertOk()
            ->assertSeeInOrder(['action="'.$store.'"', 'enctype="multipart/form-data"', 'name="photo"'], false);

        $this->post($store, [
            'name' => 'Ana',
            'photo' => UploadedFile::fake()->image('foto.jpeg', 32, 32),
        ])->assertRedirect(route('groups.edit', $group))->assertSessionHasNoErrors();

        $participant = $group->participants()->sole();
        $this->assertNotNull($participant->photo);
        Storage::disk('public')->assertExists($participant->photo);
    }

    public function test_invalid_photo_keeps_the_other_fields_in_the_group_form(): void
    {
        $this->signInOrganizer();
        $group = $this->ownedGroup(['name' => 'Grupo de teste']);
        $edit = route('groups.edit', $group);

        $this->from($edit)->post(route('participants.store', $group), [
            'name' => 'Ana',
            'email' => 'ana@example.test',
            'whatsapp' => '(11) 91234-5678',
            'wishlist' => 'Livro de receitas',
            'photo' => 'foto.jpeg',
        ])->assertRedirect($edit)->assertSessionHasErrors('photo');

        $this->assertDatabaseCount('participants', 0);
        $this->get($edit)->assertOk()
            ->assertSee('Escolha novamente uma imagem válida')
            ->assertSee('value="Ana"', false)
            ->assertSee('value="ana@example.test"', false)
            ->assertSee('value="(11) 91234-5678"', false)
            ->assertSee('value="Livro de receitas"', false);
    }
}
