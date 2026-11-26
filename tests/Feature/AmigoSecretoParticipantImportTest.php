<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\HasOrganizerGroups;
use Tests\TestCase;

class AmigoSecretoParticipantImportTest extends TestCase
{
    public function test_import_skips_only_unreadable_email_and_keeps_valid_long_email(): void
    {
        $this->signInOrganizer();
        $destination = $this->ownedGroup(['name' => 'Atual', 'event_date' => '2026-12-24']);
        $source = $this->ownedGroup(['name' => 'Anterior', 'event_date' => '2025-12-24']);
        $legacy = $source->participants()->create(['name' => 'Cadastro antigo']);
        $oldEncryption = new Encrypter(random_bytes(32), 'AES-256-CBC');
        $oldPayload = $oldEncryption->encryptString('antigo@example.test');
        DB::table('participants')->where('id', $legacy->id)->update(['email' => $oldPayload]);

        $longEmail = str_repeat('a', 64).'@'.str_repeat('b', 50).'.'.str_repeat('c', 50).'.test';
        $readable = $source->participants()->create(['name' => 'Cadastro legível', 'email' => $longEmail]);

        $this->assertNull($legacy->fresh()->email);
        $this->post(route('participants.import.store', $destination), [
            'source' => $source->id, 'participants' => [$legacy->id, $readable->id],
        ])->assertRedirect(route('groups.edit', $destination))
            ->assertSessionHas('success', '2 participantes importados.')
            ->assertSessionHas('participant_import_warning');

        $this->assertNull($destination->participants()->where('imported_from_participant_id', $legacy->id)->sole()->email);
        $copy = $destination->participants()->where('imported_from_participant_id', $readable->id)->sole();
        $this->assertSame($longEmail, $copy->email);
        $this->assertGreaterThan(255, strlen($copy->getRawOriginal('email')));
        $this->assertSame($oldPayload, $legacy->fresh()->getRawOriginal('email'));
        $this->get(route('groups.edit', $destination))->assertOk()
            ->assertSee('Não foi possível ler 1 e-mail(s)')
            ->assertDontSee($oldPayload);
    }

    use HasOrganizerGroups, RefreshDatabase;

    public function test_it_imports_selected_participants_without_carrying_over_draw_state_or_duplicating_them(): void
    {
        $this->signInOrganizer();
        $destination = $this->ownedGroup(['name' => 'Família atual', 'event_date' => '2026-12-24']);
        $source = $this->ownedGroup(['name' => 'Família anterior', 'event_date' => '2025-12-24']);
        $participant = $source->participants()->create([
            'name' => 'Ana Maria', 'gender' => 'F', 'email' => 'ana@example.test', 'whatsapp' => '11912345678',
            'photo' => 'participants/ana.jpg', 'wishlist' => 'Lista antiga', 'secret_key' => 'ANTIGO',
            'shared_gift_idea' => true, 'bought_gift' => true,
        ]);
        $participant->forceFill(['pwa_access_hash' => hash('sha256', 'codigo antigo')])->save();
        $unselected = $source->participants()->create(['name' => 'Bia']);
        $this->ownedGroup(['name' => 'Outro ano', 'event_date' => '2024-12-24']);

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
        $this->signInOrganizer();
        $destination = $this->ownedGroup(['name' => 'Atual', 'event_date' => '2026-12-24']);
        $source = $this->ownedGroup(['name' => 'Anterior', 'event_date' => '2025-12-24']);
        $unrelated = $this->ownedGroup(['name' => 'Outra família', 'event_date' => '2025-12-24']);
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
        $this->signInOrganizer();
        $destination = $this->ownedGroup(['name' => 'Atual', 'event_date' => '2026-12-24']);
        $source = $this->ownedGroup(['name' => 'Grupo privado', 'event_date' => '2025-12-24']);
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
