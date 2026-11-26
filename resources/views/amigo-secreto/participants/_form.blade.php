{{-- Nome --}}
<div class="mb-4">
    <label class="block font-semibold mb-1">Nome</label>
    <input type="text"
           name="name"
           value="{{ old('name', $participant->name ?? '') }}"
           class="w-full border rounded p-2"
           required>
    @error('name')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Foto do participante --}}
<div class="mb-4">
    <label class="block font-semibold mb-1">Foto (opcional)</label>
    <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp"
           class="w-full border rounded p-2">
    <p class="text-sm text-gray-600 mt-1">JPG, PNG, WebP, GIF ou BMP, até 2 MB.</p>

    @if(isset($participant) && $participant->photo)
        <img src="{{ asset('storage/'.$participant->photo) }}"
             class="w-20 h-20 rounded-full mt-2 border shadow">
    @endif

    @error('photo')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Email --}}
<div class="mb-4">
    <label class="block font-semibold mb-1">E-mail (opcional)</label>
    <input type="email"
           name="email"
           value="{{ old('email', $participant->email ?? '') }}"
           class="w-full border rounded p-2">
    @error('email')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- WhatsApp --}}
<div class="mb-4">
    <label class="block font-semibold mb-1">WhatsApp (opcional)</label>
    <input type="text"
           name="whatsapp"
           value="{{ old('whatsapp', $participant->whatsapp ?? '') }}"
           class="w-full border rounded p-2"
           placeholder="(11) 91234-5678">
    @error('whatsapp')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Lista de desejos --}}
<div class="mb-4">
    <label class="block font-semibold mb-1">Lista de desejos (opcional)</label>
    <textarea name="wishlist"
              rows="3"
              class="w-full border rounded p-2">{{ old('wishlist', $participant->wishlist ?? '') }}</textarea>
    @error('wishlist')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Botão --}}
<div class="mt-6">
    <button class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded w-full md:w-auto">
        {{ $submitLabel ?? 'Salvar' }}
    </button>
</div>
