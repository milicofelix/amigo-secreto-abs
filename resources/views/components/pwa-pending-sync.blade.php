@props(['participant' => null])

@if ($participant)
    <div id="pwa-participant" class="mb-4 text-sm" data-participant="{{ $participant->id }}"
         data-csrf="{{ csrf_token() }}" data-status-url="{{ route('participant.status.update', absolute: false) }}">
        <button id="pwa-gift" type="button" data-recorded="{{ $participant->bought_gift ? 'yes' : 'no' }}" hidden></button>
        <p id="pwa-sync-status" role="status"></p>
        <button id="pwa-sync-retry" type="button" class="font-semibold text-indigo-700 underline" hidden>Tentar sincronizar</button>
        <a id="pwa-sync-login" href="{{ route('pwa.participant') }}" class="font-semibold text-indigo-700 underline" hidden>
            Verificar acesso novamente
        </a>
    </div>
    <script src="{{ asset('pwa-participant.js') }}" defer></script>
@endif
