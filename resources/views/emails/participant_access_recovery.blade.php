<x-mail::message>
# Recupere seu acesso

Escolha o grupo que quer abrir. Cada link vale por 15 minutos e pode ser usado uma vez.

@foreach ($links as $link)
<x-mail::button :url="$link['url']">
{{ $link['label'] }}
</x-mail::button>
@endforeach

Se você não solicitou a recuperação, ignore este e-mail.
</x-mail::message>
