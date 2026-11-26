@extends('layouts.rifa')

@section('content')

<style>
    .pix-box{
        background:#fff;
        padding:25px;
        border-radius:12px;
        max-width:420px;
        margin:0 auto;
        box-shadow:0 4px 10px rgba(0,0,0,0.1);
    }
    .pix-qrcode{
        max-width:260px;
        margin:0 auto;
        padding:10px;
        background:#fff;
        border-radius:8px;
        border:1px solid #ddd;
    }
</style>

<div class="pix-box text-center">

    <h3 class="mb-3">Pagamento PIX</h3>

    <p><strong>Número escolhido:</strong> {{ $nome->nome }}</p>
    <p><strong>Comprador:</strong> {{ $nome->comprador_nome }}</p>
    <p><strong>WhatsApp:</strong> {{ $nome->comprador_whatsapp }}</p>

    <hr>

    <h5>Escaneie o QR Code:</h5>
    <div class="pix-qrcode">
        <img src="{{ $qrcode }}" 
            alt="QR Code PIX"
            style="max-width: 260px; width: 100%; height: auto;">
    </div>

    <h5 class="mt-4">Ou copie o código PIX:</h5>

    <textarea 
        class="form-control text-center" 
        rows="4" 
        readonly 
        id="pixCode">{{ $payload }}</textarea>

    <button onclick="copyPix()" class="btn btn-success mt-3 w-100">📋 Copiar Código PIX</button>

    <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3 w-100">Voltar</a>

</div>

<script>
function copyPix(){
    let text = document.querySelector('#pixCode');
    text.select();
    document.execCommand('copy');
    alert('Código PIX copiado!');
}
</script>

@endsection
