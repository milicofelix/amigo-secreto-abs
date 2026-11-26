@component('mail::message')
# 🎉 Olá, {{ $giver->name }}!

O sorteio do grupo **{{ $group->name }}** foi realizado com sucesso.  
E o seu amigo secreto é...

@component('mail::panel')
## 🎁 {{ $receiver->name }}
@endcomponent

@if($receiver->wishlist)
**Dica de presente:**  
{{ $receiver->wishlist }}
@endif

@isset($group->event_date)
📅 **Data do evento:** {{ \Carbon\Carbon::parse($group->event_date)->format('d/m/Y') }}  
@endisset

@isset($group->event_location)
📍 **Local:** {{ $group->event_location }}
@endisset

Boas compras e um ótimo evento! 😄

@endcomponent
