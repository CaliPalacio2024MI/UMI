<div id="viewCareerModal_{{ $career->id }}" class="modal-overlay modal-view-career">
    <div class="modal-content-container modal-view-career__container">
        <div class="modal-header-custom modal-view-career__header">
            <h5 id="viewCareerModalLabel" class="modal-view-career__title">Informacion de Carrera</h5>
            <button type="button" class="close-custom btn-close-view modal-view-career__close" aria-label="Cerrar">&times;</button>
        </div>
        <div class="modal-body-custom modal-view-career__body">
            <dl class="career-view-dl career-view-dl--styled">
                <div class="career-view-row">
                    <dt>Nombre:</dt>
                    <dd>{{ $career->name }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>RVOE (Acuerdo número)</dt>
                    <dd>{{ $career->official_id ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Clasificación</dt>
                    <dd>{{ $career->classification->name ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Descripción</dt>
                    <dd>{{ collect([$career->description1, $career->description2, $career->description3])->filter()->implode(' / ') ?: '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Modalidad</dt>
                    <dd>{{ $career->type ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>No. de semestres</dt>
                    <dd>{{ $career->semesters ?? '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Configuración de mensualidad</dt>
                    <dd>{{ ($career->pricing_mode ?? 'uniform') === 'per_month' ? 'Precio por mes' : 'Precio único' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Monto mensualidad</dt>
                    <dd>{{ ($career->pricing_mode ?? 'uniform') === 'uniform' && $career->monto_mensualidad !== null ? '$' . number_format((float) $career->monto_mensualidad, 2) : 'Variable por mes' }}</dd>
                </div>
                @if(($career->pricing_mode ?? 'uniform') === 'per_month')
                    <div class="career-view-row">
                        <dt>Detalle por mes</dt>
                        <dd>
                            @php
                                $prices = is_array($career->monthly_prices) ? $career->monthly_prices : [];
                            @endphp
                            {{ collect($prices)->map(fn ($value, $month) => "Mes {$month}: $" . number_format((float) $value, 2))->implode(' | ') ?: '—' }}
                        </dd>
                    </div>
                @endif
                <div class="career-view-row">
                    <dt>Precio total carrera</dt>
                    <dd>
                        @php
                            $precioTotal = ($career->pricing_mode ?? 'uniform') === 'per_month'
                                ? (float) collect(is_array($career->monthly_prices) ? $career->monthly_prices : [])->sum()
                                : ((float) ($career->monto_mensualidad ?? 0) * (int) ($career->semesters ?? 0));
                        @endphp
                        {{ '$' . number_format($precioTotal, 2) }}
                    </dd>
                </div>
                <div class="career-view-row">
                    <dt>Porcentaje de cargo moratorio</dt>
                    <dd>{{ $career->porcentaje_cargo_moratorio !== null ? number_format((float) $career->porcentaje_cargo_moratorio, 2) . '%' : '0.00%' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Cargo moratorio</dt>
                    <dd>{{ $career->cargo_monetario !== null ? '$' . number_format((float) $career->cargo_monetario, 2) : '—' }}</dd>
                </div>
                <div class="career-view-row">
                    <dt>Fecha vencimiento (asignada por sistema)</dt>
                    <dd>
                        @if($career->fecha_vencimiento_moratorio)
                            {{ \Carbon\Carbon::parse($career->fecha_vencimiento_moratorio)->format('d/m/Y') }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
