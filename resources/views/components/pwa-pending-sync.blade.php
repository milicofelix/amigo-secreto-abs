@props(['participant' => null])

@php($drawId = $participant?->activeAssignments()->latest('id')->value('draw_id'))

@if ($drawId)
    <div id="pwa-participant" class="mb-4 text-sm" data-participant="{{ $participant->id }}"
         data-draw-id="{{ $drawId }}" data-invite-scope="{{ $participant->inviteScope() }}"
         data-csrf="{{ csrf_token() }}" data-status-url="{{ route('participant.status.update', absolute: false) }}">
        <button id="pwa-gift" type="button" data-recorded="{{ $participant->bought_gift ? 'yes' : 'no' }}" hidden></button>
        <p id="pwa-sync-status" role="status"></p>
        <button id="pwa-sync-retry" type="button" class="font-semibold text-indigo-700 underline" hidden>Tentar sincronizar</button>
        <a id="pwa-sync-login" href="{{ route('pwa.participant') }}" class="font-semibold text-indigo-700 underline" hidden>
            Verificar acesso novamente
        </a>
    </div>
    <script src="{{ asset('pwa-participant.js') }}?v=6" defer></script>
@endif
