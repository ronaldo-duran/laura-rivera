{{-- Propuestas por eje. Todo el contenido está en el HTML (rastreable); JS solo alterna la vista. --}}
@php $ejes = config('laura.ejes'); @endphp
<div class="tabs" data-tabs>
    <div class="tab-list" role="tablist" aria-label="Ejes de propuestas">
        @foreach ($ejes as $i => $eje)
            <button type="button" role="tab" class="tab" id="tab-{{ $eje['id'] }}"
                    aria-controls="panel-{{ $eje['id'] }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}"
                    tabindex="{{ $i === 0 ? '0' : '-1' }}" data-tab="{{ $eje['id'] }}">{{ $eje['nombre'] }}</button>
        @endforeach
    </div>
    @foreach ($ejes as $i => $eje)
        <section class="tab-panel grid-2" role="tabpanel" id="panel-{{ $eje['id'] }}" aria-labelledby="tab-{{ $eje['id'] }}"
                 data-panel="{{ $eje['id'] }}" @if($i !== 0) hidden @endif>
            <div class="stack-sm">
                <h3 class="h3">{{ $eje['nombre'] }}</h3>
                <p class="lead">{{ $eje['intro'] }}</p>
            </div>
            <ol class="numbered">
                @foreach ($eje['items'] as $n => $item)
                    <li><span class="num">{{ str_pad($n + 1, 2, '0', STR_PAD_LEFT) }}</span><span>{{ $item }}</span></li>
                @endforeach
            </ol>
        </section>
    @endforeach
</div>
