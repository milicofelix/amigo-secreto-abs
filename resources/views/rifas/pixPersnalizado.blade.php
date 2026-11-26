@extends('layouts.rifa')

@section('content')

<style>

    /* Fundo geral suave em lilás */
    body {
        background: #f3e6ff !important;
    }

    /* Container principal */
    .pix-box{
        background: #ffffff;
        padding: 35px;
        border-radius: 18px;
        max-width: 520px;
        margin: 40px auto;
        border: 3px solid #d3b4ff;
        box-shadow: 0 0 22px rgba(185, 140, 255, 0.35);
    }

    /* Logo Ricosty */
    .logo-ricosty{
        width: 140px;
        margin-bottom: 10px;
    }

    /* Título */
    .pix-title{
        font-size: 30px;
        font-weight: 800;
        color: #8c4edc;
        margin-bottom: 5px;
    }

    /* Subtítulo */
    .pix-sub{
        font-size: 18px;
        font-weight: 500;
        color: #b17cf7;
        margin-bottom: 20px;
    }

    /* Estilo do QR Code */
    .pix-qrcode{
        background:#fff;
        border-radius: 12px;
        border: 3px solid #d8c4ff;
        padding: 15px;
        margin: 0 auto 15px auto;
        max-width: 260px;
    }

    /* WhatsApp Box */
    .zap-box{
        margin-top: 25px;
        padding: 12px;
        background: #f8f1ff;
        border-radius: 12px;
        border: 2px solid #e6d5ff;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .zap-icon{
        width: 32px;
    }

    .zap-text{
        font-size: 17px;
        font-weight: 600;
        color: #6d3bbf;
    }

</style>


<div class="pix-box text-center">

    {{-- LOGO RICOSTY --}}
    <img src="{{ asset('images/logo-ricosty.png') }}" alt="Logo Ricosty" width="200" height="150" class="logo-ricosty">

    <h2 class="pix-title">Pagamento via PIX</h2>
    <div class="pix-sub">Ricosty • Emagrecimento e Estética</div>

    <h5 style="color:#7a32cf; font-weight:600;">Escaneie o QR Code:</h5>

    <div class="pix-qrcode">
        <img src="{{ $qrcode }}" 
            alt="QR Code PIX"
            style="max-width: 260px; width: 100%; height: auto;">
    </div>

    {{-- Informações adicionais --}}
    <small style="color:#a279e9; font-size:15px;">
        Pagamento rápido, seguro e sem taxas.
    </small>

    {{-- WhatsApp --}}
    <div class="zap-box">
        <img src="{{asset('images/whatsapp-icon.png')}}" class="zap-icon">
        <div class="zap-text">Confirme seu pagamento no WhatsApp</div>
    </div>

</div>

@endsection
