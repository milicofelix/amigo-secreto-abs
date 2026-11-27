@php($member = request()->attributes->get('pwa.participant'))
@if ($member)
    <a href="{{ route('amigo.dashboard', $member->group_id, absolute: false) }}">Dashboard do grupo</a>
    <a href="{{ route('draw.show', $member->group_id, absolute: false) }}">Sorteio</a>
    <a href="{{ route('pwa.participant', absolute: false) }}">Meu Amigo Secreto</a>
@endif
