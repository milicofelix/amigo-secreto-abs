@extends('layouts.amigo-secreto')

@section('title', 'Amigos Revelados')

@section('content')

<h1 class="text-2xl font-bold text-indigo-700 mb-6">
    🎁 Amigos revelados — {{ $group->name }}
</h1>

@if($results->isEmpty())
    <div class="bg-yellow-100 text-yellow-800 p-4 rounded-lg">
        Nenhum amigo secreto foi revelado ainda.
    </div>
@else

<div class="flex flex-col gap-4">

    @foreach($results as $row)
        <div
            class="flex items-center justify-between gap-4
                   bg-white rounded-xl shadow-sm border
                   p-4 hover:shadow-md transition">

            {{-- Quem tirou --}}
            <div class="flex items-center gap-3 min-w-0">
                <div
                    class="w-10 h-10 rounded-full bg-indigo-100
                           flex items-center justify-center
                           font-bold text-indigo-700">
                    {{ mb_substr($row->quem_tirou, 0, 1) }}
                </div>

                <div class="truncate">
                    <div class="text-sm text-gray-500">Quem tirou</div>
                    <div class="font-semibold text-gray-800 truncate">
                        {{ $row->quem_tirou }}
                    </div>
                </div>
            </div>

            {{-- Ícone --}}
            <div class="text-indigo-500 text-xl shrink-0">
                🎁
            </div>

            {{-- Quem foi tirado --}}
            <div class="flex items-center gap-3 min-w-0">
                <div
                    class="w-10 h-10 rounded-full bg-green-100
                           flex items-center justify-center
                           font-bold text-green-700">
                    {{ mb_substr($row->quem_foi_tirado, 0, 1) }}
                </div>

                <div class="truncate text-right">
                    <div class="text-sm text-gray-500">Quem foi tirado</div>
                    <div class="font-semibold text-gray-800 truncate">
                        {{ $row->quem_foi_tirado }}
                    </div>
                </div>
            </div>

        </div>
    @endforeach

</div>

@endif

@endsection
