@extends('layouts.amigo-secreto')

@section('content')
<div id="qrCard" class="max-w-sm mx-auto mt-10 p-6 bg-gradient-to-br from-indigo-300 to-indigo-50 rounded-2xl shadow-2xl text-center border border-indigo-400 relative">

    {{-- FOTO / AVATAR --}}
    <div class="w-24 h-24 mx-auto rounded-full border-4 border-white shadow bg-white flex items-center justify-center text-5xl">
        @if(filter_var($photo, FILTER_VALIDATE_URL))
            <img src="{{ $photo }}" class="w-full h-full rounded-full object-cover">
        @else
            <span>{{ $photo }}</span>
        @endif
    </div>

    <h2 class="text-2xl font-extrabold text-indigo-900 mt-3">{{ $participant->name }}</h2>
    <p class="text-sm text-gray-600">🎁 {{ $group->name }}</p>

    <div class="bg-white border rounded-xl shadow-xl p-4 mx-auto mt-5 w-fit">
        <img src="data:image/png;base64,{{ $qrBase64 }}" class="w-56 mx-auto rounded-md">
    </div>

    <p class="mt-3 text-xs text-gray-500">🔒 Uso exclusivo — Não compartilhe!</p>

    <button id="downloadQR"
        class="mt-6 px-5 py-2 bg-indigo-600 text-white rounded-lg font-semibold shadow hover:bg-indigo-700 transition">
        💾 Salvar como imagem
    </button>

    <a href="{{ route('draw.show', $group, absolute: false) }}"
       class="block mt-3 text-indigo-500 hover:text-indigo-700 font-semibold text-sm">
        ← Voltar ao sorteio
    </a>
</div>

{{-- SCRIPT DOWNLOAD --}}
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script>
document.getElementById('downloadQR').addEventListener('click', function () {
    html2canvas(document.querySelector("#qrCard")).then(canvas => {
        const a = document.createElement("a");
        a.href = canvas.toDataURL("image/png");
        a.download = "QR_{{ $participant->name }}.png";
        a.click();
    });
});
</script>
@endsection
