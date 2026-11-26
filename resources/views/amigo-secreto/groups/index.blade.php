@extends('layouts.amigo-secreto')

@section('content')
<div class="max-w-5xl mx-auto mt-6 px-4">

    <h1 class="text-2xl md:text-3xl font-bold mb-6 text-black">
        🎁 Meus Grupos de Amigo Secreto
    </h1>

    <a href="{{ route('groups.create', ['year' => $selectedYear]) }}"
       class="bg-green-600 text-white px-4 py-2 rounded mb-6 inline-block hover:bg-green-700">
        + Novo Grupo
    </a>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif


    {{-- LISTAGEM DOS GRUPOS --}}
    @forelse ($groups as $group)
        <div class="bg-white shadow rounded p-4 mb-4">
            <h2 class="font-semibold text-xl">{{ $group->name }}</h2>
            <p class="text-gray-600">{{ $group->description }}</p>

            <p class="text-sm text-gray-500 mt-2">
                <strong>Data do evento:</strong> {{ $group->event_date ?? 'não definida' }} <br>
                <strong>Local:</strong> {{ $group->event_location ?? 'não informado' }}
            </p>

            <div class="mt-4 flex flex-col sm:flex-row sm:items-center gap-2">

                {{-- GERENCIAR --}}
                <a href="{{ route('groups.edit', $group) }}"
                   class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 w-full sm:w-auto text-center">
                    Gerenciar
                </a>

                {{-- VER RESULTADO --}}
                @if($group->draws()->where('status', 'completed')->exists())
                    <a href="{{ route('draw.show', ['group' => $group->id]) }}"
                       class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700 w-full sm:w-auto text-center text-sm">
                        👁️ Ver Resultado
                    </a>
                @endif

                {{-- EXCLUIR – CHAMA O MODAL --}}
                <form method="POST"
                      action="{{ route('groups.destroy', $group) }}"
                      class="w-full sm:w-auto delete-form">
                    @csrf
                    @method('DELETE')

                    <button type="button"
                            onclick="openDeleteModal(this.closest('form'))"
                            class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 w-full">
                        Excluir
                    </button>
                </form>

            </div>
        </div>
    @empty
        <p class="rounded bg-white p-4 text-gray-700">Nenhum grupo em {{ $selectedYear }}.</p>
    @endforelse



    {{-- 🔐 MODAL GLOBAL (NÃO FICA DENTRO DO LOOP!) --}}
    <div id="deleteModal"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white p-6 rounded shadow-lg w-80 text-center">

            <h2 class="text-lg font-bold mb-4">Confirmar Exclusão</h2>
            <p class="text-gray-600 mb-4">Digite o código secreto para excluir:</p>

            <input id="deleteCode" type="password"
                   class="w-full border rounded p-2 mb-4"
                   placeholder="Código secreto">

            <div class="flex justify-between gap-2">
                <button onclick="closeDeleteModal()"
                        class="bg-gray-300 px-4 py-2 rounded w-full">
                    Cancelar
                </button>

                <button onclick="submitDeleteForm()"
                        class="bg-red-600 text-white px-4 py-2 rounded w-full">
                    Excluir
                </button>
            </div>
        </div>
    </div>


    {{-- 🔐 SCRIPT DO MODAL --}}
    <script>
        let currentForm = null;

        function openDeleteModal(form) {
            currentForm = form;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteCode').value = "";
        }

        function submitDeleteForm() {
            const code = document.getElementById('deleteCode').value;

            if (code !== "{{ env('DELETE_SECRET_CODE') }}") {
                alert("Código inválido!");
                return;
            }

            currentForm.submit();
        }
    </script>

</div>
@endsection
