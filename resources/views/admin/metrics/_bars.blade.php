{{-- Lista con barras horizontales. $rows: colección de objetos con ->label/->key y ->total --}}
@php $max = max(1, $rows->max('total') ?? 1); @endphp
@if ($rows->isEmpty())
    <p class="muted small">Sin datos en este periodo.</p>
@else
    <ul class="bars">
        @foreach ($rows as $row)
            <li>
                <span class="bars-label" title="{{ $row->label ?? $row->key }}">{{ ($row->label ?? $row->key) ?: ($empty ?? '—') }}</span>
                <span class="bars-track"><span class="bars-fill" style="width: {{ max(2, round($row->total / $max * 100)) }}%"></span></span>
                <span class="bars-value">{{ number_format($row->total, 0, ',', '.') }}</span>
            </li>
        @endforeach
    </ul>
@endif
