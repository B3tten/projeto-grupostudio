{{-- Carried by every mutating form so the response can rebuild the filtered listing. --}}
@foreach (($filtros ?? []) as $campo => $valor)
    @if (is_string($valor) && $valor !== '')
        <input type="hidden" name="_filtros[{{ $campo }}]" value="{{ $valor }}">
    @endif
@endforeach
<input type="hidden" name="_page" value="{{ $pagina ?? 1 }}">
