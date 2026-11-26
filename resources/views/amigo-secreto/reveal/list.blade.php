@extends('layouts.amigo-secreto')

@section('content')
<style>
    .secret-card {
        background: linear-gradient(135deg, #2d6cdf, #4c9bff);
        border-radius: 12px;
        padding: 18px 25px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        color: #fff;
    }

    .secret-title {
        font-weight: 700;
        font-size: 1.6rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .reveal-item {
        background: #f2f7ff;
        padding: 12px 18px;
        border-radius: 10px;
        border-left: 5px solid #2d6cdf;
        margin-bottom: 12px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        color: #0d2b63;
    }

    .reveal-date {
        font-size: 0.9rem;
        color: #445e8c;
        margin-top: 4px;
    }

    .no-reveal-alert {
        background: #e3f0ff;
        border: 1px solid #cfe0ff;
        border-radius: 10px;
        font-size: 1rem;
        box-shadow: 0 3px 7px rgba(0,0,0,0.08);
        color: #0d2b63;
    }

    .secret-back-btn {
        border-radius: 25px;
        padding: 10px 20px;
        font-weight: 600;
    }
</style>

<div class="container mt-4">

    {{-- Header --}}
    <div class="secret-card mb-4">
        <div class="secret-title">
            🔎 Revelações do Grupo: <strong>{{ $group->name }}</strong>
        </div>
    </div>

    {{-- Feedback / Lista --}}
    @if($revealed->isEmpty())
        <div class="alert no-reveal-alert text-center">
            😕 Ainda ninguém revelou quem tirou. Aguarde as surpresas!
        </div>
    @else
        @foreach($revealed as $item)
            <div class="reveal-item">
                <strong>{{ $item->participant->name }}</strong> já sabe quem tirou no Amigo Secreto! 🎁
                <div class="reveal-date">
                    📅 Revelado em: <strong>{{ $item->revealed_at->format('d/m/Y H:i') }}</strong>
                </div>
            </div>
        @endforeach
    @endif

    {{-- Voltar --}}
    <div class="mt-3">
        <a href="{{ route('amigo.dashboard', $group, absolute: false) }}" class="btn btn-primary secret-back-btn">
            ⬅ Voltar ao dashboard
        </a>
    </div>

</div>
@endsection
