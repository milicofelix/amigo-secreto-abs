<?php

namespace Tests\Feature;

use App\Models\AmigoSecreto\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmigoSecretoParticipantImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_selected_participants_without_carrying_over_draw_state_or_duplicating_them(): void
    {
        $destination = Group::create(['name' => 'Família atual', 'event_date' => '2026-12-24']);
        $source = Group::create(['name' => 'Família anterior', 'event_date' => '2025-12-24']);
        $participant = $source->participants()->create([
            'name' => 'Ana Maria', 'gender' => 'F', 'email' => 'ana@example.test', 'whatsapp' => '11912345678',
            'photo' => 'participants/ana.jpg', 'wishlist' => 'Lista antiga', 'secret_key' => 'ANTIGO',
            'shared_gift_idea' => true, 'bought_gift' => true,
        ]);
        $participant->forceFill(['pwa_access_hash' => hash('sha256', 'codigo antigo')])->save();
        $unselected = $source->participants()->create(['name' => 'Bia']);
        Group::create(['name' => 'Outro ano', 'event_date' => '2024-12-24']);

        $this->get(route('groups.edit', $destination))->assertOk()->assertSee('Importar do ano anterior');
        $this->get(route('participants.import', $destination))->assertOk()
            ->assertSee('Importar participantes de 2025')->assertSee('Família anterior')
            ->assertSee('Ana Maria')->assertSee('Bia')->assertDontSee('Outro ano');

        $url = route('participants.import.store', $destination);
        $payload = ['source' => $source->id, 'participants' => [$participant->id]];
        $this->post($url, $payload)->assertRedirect(route('groups.edit', $destination))
            ->assertSessionHas('success', '1 participante importado.');

        $copy = $destination->participants()->sole();
        $this->assertEquals($participant->only(['name', 'gender', 'email', 'whatsapp', 'photo']),
            $copy->only(['name', 'gender', 'email', 'whatsapp', 'photo']));
        $this->assertNotEmpty($copy->secret_key);
        $this->assertNotSame($participant->secret_key, $copy->secret_key);
        $this->assertNull($copy->pwa_access_hash);
        $this->assertNull($copy->wishlist);
        $this->assertFalse((bool) $copy->bought_gift);
        $this->assertFalse((bool) $copy->shared_gift_idea);

        $copy->update(['name' => 'Ana atualizada']);
        $this->post($url, $payload)->assertSessionHas('success', '0 participantes importados. 1 cadastro já existente foi ignorado.');
        $this->assertSame(1, $destination->participants()->count());
        $this->assertSame('Ana Maria', $participant->fresh()->name);
        $this->assertTrue($source->participants()->whereKey($unselected->id)->exists());
    }

    public function test_it_rejects_participants_outside_the_source_and_imports_after_no_completed_draw(): void
    {
        $destination = Group::create(['name' => 'Atual', 'event_date' => '2026-12-24']);
        $source = Group::create(['name' => 'Anterior', 'event_date' => '2025-12-24']);
        $unrelated = Group::create(['name' => 'Outra família', 'event_date' => '2025-12-24']);
        $valid = $source->participants()->create(['name' => 'Ana']);
        $invalid = $unrelated->participants()->create(['name' => 'Bia']);
        $url = route('participants.import.store', $destination);

        $this->post($url, ['source' => $source->id, 'participants' => [$valid->id, $invalid->id]])
            ->assertSessionHasErrors('source');
        $this->assertSame(0, $destination->participants()->count());

        $destination->draws()->create(['status' => 'completed', 'drawn_at' => now()]);
        $this->post($url, ['source' => $source->id, 'participants' => [$valid->id]])
            ->assertSessionHasErrors('source');
        $this->assertSame(0, $destination->participants()->count());
    }

    public function test_a_group_owned_by_another_user_is_not_an_import_source(): void
    {
        $destination = Group::create(['name' => 'Atual', 'event_date' => '2026-12-24']);
        $source = Group::create(['name' => 'Grupo privado', 'event_date' => '2025-12-24']);
        $source->forceFill(['user_id' => User::factory()->create()->id])->save();
        $participant = $source->participants()->create(['name' => 'Ana']);

        $this->get(route('participants.import', ['group' => $destination, 'source' => $source]))
            ->assertOk()->assertDontSee('Grupo privado')->assertDontSee('Ana');
        $this->post(route('participants.import.store', $destination), [
            'source' => $source->id, 'participants' => [$participant->id],
        ])->assertSessionHasErrors('source');
        $this->assertSame(0, $destination->participants()->count());
    }
}
