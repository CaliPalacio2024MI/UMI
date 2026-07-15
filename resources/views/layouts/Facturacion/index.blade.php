@extends('layouts.app')

@section('title', 'Estado de cuenta')

@section('content')

    <div class="main-header"> <h1>Estado de cuenta</h1> </div>

    {{-- === Formulario de Filtros === --}}
    <form action="{{ route('Facturacion.index') }}" method="GET">
        <div class="filter-controls">
            <div class="filter-group">
                <div class="filter-item">
                    <label for="search">Buscar</label>
                    <input type="text" id="search" name="search" class="search-input" placeholder="Nombre, Concepto..." value="{{ request('search') }}">
                </div>
                <div class="filter-item">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="filter-select">
                        <option value="">TODOS</option>
                        <option value="Pendiente" @selected(request('status') == 'Pendiente')>Pendiente</option>
                        <option value="Abonado" @selected(request('status') == 'Abonado')>Abonado</option>
                        <option value="Pagada" @selected(request('status') == 'Pagada')>Pagada</option>
                    </select>
                </div>
                @if(isset($periods))
                <div class="filter-item">
                    <label for="period_id">Período</label>
                    <select id="period_id" name="period_id" class="filter-select">
                        <option value="">Todos</option>
                        @foreach ($periods as $period)
                            <option value="{{ $period->id }}" @selected(request('period_id') == $period->id)>{{ $period->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            
            {{-- GRUPO DE ACCIONES --}}
                <div class="action-group">
                <button type="submit" class="btn-primary">Filtrar</button>
                <a href="{{ route('Facturacion.index') }}" class="btn-secondary">Limpiar</a>
                
                @if(Auth::user()->hasActiveRole('master') || Auth::user()->hasActiveRole('control_administrativo'))
                    <button type="button" class="btn-primary" onclick="submitExport()">Exportar</button>
                @endif
            </div>
        </div>
    </form>

    {{-- === Contenido Principal === --}}
    <div class="scrollable-content-area">
        @if(isset($usersWithBillings)) 
            {{-- VISTA ADMINISTRADOR --}}
            <div class="period-accordion">
                @foreach ($periods as $period)
                    @php
                        $usersInThisPeriod = $usersWithBillings; 
                        if (request('period_id') && request('period_id') != $period->id) continue;
                        if (empty($period->meses_calculados)) continue;
                    @endphp

                    <details id="period-{{ $period->id }}" @if(request('period_id') == $period->id) open @endif>
                        <summary>
                            <span class="period-name">{{ $period->name }}</span>
                            <span class="period-info">({{ count($period->meses_calculados) }} Mensualidades)</span>
                            <span class="arrow-icon">▼</span>
                        </summary>
                        
                        <div class="period-details">
                            <div class="user-accordion">
                                @foreach ($usersInThisPeriod as $u)
                                    @php $userBillings = $u->billings->where('period_id', $period->id); @endphp
                                    @if(request('status') && $userBillings->isEmpty())
                                        @continue
                                    @endif
                                    @if(!$u->is_active && $userBillings->isEmpty())
                                        @continue
                                    @endif
                                    <details id="factura-target-user-{{ $u->id }}-period-{{ $period->id }}">
                                        <summary>
                                        <div class="user-container">
                                            <span class="user-name">{{ $u->nombre }} {{ $u->apellido_paterno }} {{ $u->apellido_materno }}</span>
                                            <span class="user-email">{{ $u->email }}</span>
                                            </div>
                                            <div>
                                                <span class="user-role">{{ $u->roles->pluck('display_name')->join(', ') }}</span>
                                                <span class="arrow-icon">▼</span>
                                            </div>
                                        </summary>
                                        <div class="user-details">
                                            {{-- ITERACIÓN DE MESES Y FACTURAS --}}
                                            <div class="months-container">
                                                @foreach ($period->meses_calculados as $mes)
                                                    @php
                                                        $facturasDelMes = $userBillings->filter(function($b) use ($mes) {
                                                            return \Carbon\Carbon::parse($b->fecha_vencimiento)->format('Y-m') === $mes['key'];
                                                        });
                                                        /** Solo ocultar «mensualidad» si ya hay una factura MEN- ese mes (otras: INS-, EXT-, etc. no bloquean). */
                                                        $yaHayMensualidadEsteMes = $facturasDelMes->contains(function ($b) {
                                                            $uid = (string) ($b->factura_uid ?? '');
                                                            return str_starts_with($uid, 'MEN-');
                                                        });
                                                    @endphp

                                                    <div class="monthly-block">
                                                        <details class="monthly-block-details" open>
                                                        <summary class="month-header">
                                                            <strong>{{ $mes['label'] }}</strong>
                                                            @if($period->is_active == 1)
                                                                <div class="month-header__actions">
                                                                    <button type="button" class="btn-sm-add btn-open-extra js-trigger-factura"
                                                                        data-user-id="{{ $u->id }}"
                                                                        data-user-name="{{ $u->nombre }} {{ $u->apellido_paterno }}"
                                                                        data-period-id="{{ $period->id }}"
                                                                        data-uid-prefix="EXT-">
                                                                        + Agregar Factura Extra
                                                                    </button>
                                                                    @if(! $yaHayMensualidadEsteMes)
                                                                        <button type="button" class="btn-sm-add btn-open-specific js-trigger-factura"
                                                                            data-user-id="{{ $u->id }}"
                                                                            data-user-name="{{ $u->nombre }} {{ $u->apellido_paterno }}"
                                                                            data-period-id="{{ $period->id }}"
                                                                            data-uid-prefix="MEN-"
                                                                            data-date="{{ $mes['date'] }}"
                                                                            data-label="{{ $mes['label'] }}">
                                                                            + Agregar mensualidad
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                @if($facturasDelMes->isNotEmpty())
                                                                    @php
                                                                        $todasPagadas = $facturasDelMes->every(fn($b) => $b->computed_status === 'Pagada');
                                                                        $algunaAbonada = $facturasDelMes->contains(fn($b) => $b->computed_status === 'Abonado');
                                                                    @endphp
                                                                    @if($todasPagadas)
                                                                        <span style="font-size: 0.8rem; color: #28a745; font-weight: 600;">✓ Pagada</span>
                                                                    @elseif($algunaAbonada)
                                                                        <span style="font-size: 0.8rem; color: #ffc107; font-weight: 600;">◐ Abonado</span>
                                                                    @else
                                                                        <span style="font-size: 0.8rem; color: #dc3545; font-weight: 600;">✗ Sin pago</span>
                                                                    @endif
                                                                @else
                                                                    <span style="font-size: 0.8rem; color: #999; font-weight: 600;">— Sin factura</span>
                                                                @endif
                                                            @endif
                                                        </summary>

                                                        @if($facturasDelMes->isNotEmpty())
                                                            <div class="table-container">
                                                                <table class="main-table">
                                                                    <thead>
                                                                        <tr>
                                                                            <th width="15%">Folio</th>
                                                                            <th width="20%">Concepto</th>
                                                                            <th width="15%">Monto</th>
                                                                            <th width="15%">Vence</th>
                                                                            <th width="15%">Estado</th>
                                                                            <th width="20%">Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="billing-item-tbody">
                                                                        @foreach($facturasDelMes as $billing)
                                                                            @php
                                                                                $estatus = $billing->computed_status;
                                                                                $totalPagado = $billing->payments->sum('monto');
                                                                                $saldo = $billing->monto - $totalPagado;
                                                                                $estatusIcono = match($estatus) { 'Pagada' => 'circulo_verde.png', 'Abonado' => 'circulo_amarillo.png', default => 'circulo_rojo.png' };
                                                                                $colorStatus = match($estatus) { 'Pagada' => '#28a745', 'Abonado' => '#ffc107', default => '#dc3545' };
                                                                            @endphp
                                                                            <tr class="billing-main-row">
                                                                                <td>{{ $billing->factura_uid }}</td>
                                                                                <td>{{ $billing->concepto }}</td>
                                                                                <td>
                                                                                    ${{ number_format($billing->monto, 2) }}
                                                                                    @if($estatus == 'Abonado') <br><small style="color:#e8a800">Saldo: ${{number_format($saldo,2)}}</small> @endif
                                                                                </td>
                                                                                <td>{{ \Carbon\Carbon::parse($billing->fecha_vencimiento)->format('d/m/Y') }}</td>
                                                                                <td style="font-weight:500;">
                                                                                    <div class="estado">
                                                                                        <img src="{{ asset('images/icons/'.$estatusIcono) }}" alt="{{$estatus}}" class="estado-icono" draggable="false" oncontextmenu="return false;">
                                                                                        <span style="color: {{ $colorStatus }}">{{ $estatus }}</span>
                                                                                    </div>
                                                                                </td>
                                                                                <td class="acciones">
                                                                                   <svg class="icon icon-toggle" title="Ver Abonos" style="cursor:pointer; width:20px; height:20px; vertical-align:middle;" viewBox="0 0 24 24" fill="#223F70" xmlns="http://www.w3.org/2000/svg" draggable="false"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                                                                                    @if($billing->archivo_path)<a href="{{ Storage::url($billing->archivo_path) }}" target="_blank"><img src="{{ asset('images/icons/pdf.png') }}" class="icon" draggable="false"></a>@endif
                                                                                    @if($billing->xml_path)<a href="{{ Storage::url($billing->xml_path) }}" target="_blank"><img src="{{ asset('images/icons/xml.png') }}" class="icon" draggable="false"></a>@endif
                                                                                     
                                                                                   <form action="{{ route('Facturacion.destroy', $billing->id) }}" 
                                                                                    method="POST" 
                                                                                    class="form-eliminar" 
                                                                                    data-uid="{{ $billing->factura_uid }}" 
                                                                                    style="display:inline-flex; align-items:center; vertical-align:middle;">
                                                                                    @csrf 
                                                                                    @method('DELETE')
                                                                                    <button type="submit" style="background:none; border:none; padding:0; cursor:pointer;" title="Eliminar">
                                                                                    <img src="{{ asset('images/icons/Vector.svg') }}" class="icon" draggable="false" style="width:22px; height:22px; vertical-align:middle;">
                                                                                    </button>
                                                                                    </form>
                                                                                </td>
                                                                            </tr>
                                                                            {{-- Fila Detalles de Pagos --}}
                                                                            <tr class="payment-details-row" style="display:none;">
                                                                                <td colspan="6" class="payment-details-cell">
                                                                                    @if($billing->cargo_moratorio_aplicado)
                                                                                    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 12px 15px; margin-bottom: 10px;">
                                                                                        <h4 style="margin:0 0 8px; color:#856404;">⚠️ Cargo Moratorio Aplicado</h4>
                                                                                        <p style="margin:2px 0; font-size:0.9em; color:#856404;">
                                                                                            <strong>Porcentaje:</strong> {{ $billing->porcentaje_cargo_moratorio }}% |
                                                                                            <strong>Cargo:</strong> ${{ number_format($billing->cargo_monetario, 2) }} |
                                                                                            <strong>Aplicado:</strong> {{ \Carbon\Carbon::parse($billing->fecha_cargo_moratorio)->format('d/m/Y') }}
                                                                                            @if($billing->fecha_prorroga_fin)
                                                                                            | <strong>Prórroga hasta:</strong> {{ \Carbon\Carbon::parse($billing->fecha_prorroga_fin)->format('d/m/Y') }}
                                                                                            @endif
                                                                                        </p>
                                                                                    </div>
                                                                                    @endif
                                                                                    <div class="payment-history"><h4>Historial</h4>
                                                                                        @if($billing->payments->isNotEmpty())
                                                                                            <ul> @foreach($billing->payments as $payment) <li> <span class="payment-date">{{ \Carbon\Carbon::parse($payment->fecha_pago)->format('d/m/Y') }} - {{ $payment->nota ?? 'Abono' }}</span> <span class="payment-amount">- ${{ number_format($payment->monto, 2) }}</span> </li> @endforeach </ul>
                                                                                        @else <p>Sin abonos</p> @endif
                                                                                    </div>
                                                                                    @if($estatus !== 'Pagada')
                                                                                        <div class="add-payment-form">
                                                                                            <h4>Añadir Abono</h4>
                                                                                            <form action="{{ route('payments.store') }}" method="POST">
                                                                                                @csrf <input type="hidden" name="billing_id" value="{{ $billing->id }}">
                                                                                                <input type="hidden" name="billing_id" value="{{ $billing->id }}">
                                                                                                <label for="monto_abono_{{ $billing->id }}">Monto a abonar:</label>
                                                                                                <input type="number" id="monto_abono_{{ $billing->id }}" name="monto_abono" step="0.01" max="{{ $saldo }}" placeholder="Máx: ${{ number_format($saldo, 2) }}" required>
                                                                                                <label for="fecha_pago_{{ $billing->id }}">Fecha del pago:</label>
                                                                                                <input type="date" id="fecha_pago_{{ $billing->id }}" name="fecha_pago" value="{{ date('Y-m-d') }}" required>
                                                                                                <label for="nota_abono_{{ $billing->id }}">Nota (Opcional):</label>
                                                                                                <textarea id="nota_abono_{{ $billing->id }}" name="nota_abono" rows="2" placeholder="Ej. Transferencia"></textarea>
                                                                                                <button type="submit" class="guardar-abono">Guardar Abono</button>
                                                                                            </form>
                                                                                        </div>
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @endif
                                                        </details>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        </div>
                    </details>
                @endforeach
            </div>

        @elseif(isset($billings) && isset($periods)) 
            {{-- VISTA ALUMNO --}}

            <div class="period-accordion">
                 @foreach ($periods as $period)
                    @php
                        $allBillingsForPeriod = $billings->where('period_id', $period->id);
                        if (request('period_id') && request('period_id') != $period->id) continue;
                    @endphp
                    
                    <details id="period-{{ $period->id }}" @if(request('period_id') == $period->id) open @endif>
                        {{-- CAMBIO: Summary color Azul y texto blanco --}}
                        <summary class="period-summary period-summary--blue">
                            <span class="period-name">{{ $period->name }}</span>
                            <span class="period-info">({{ count($period->meses_calculados) }} Mensualidades)</span>
                            <span class="arrow-icon">▼</span>
                        </summary>
                        
                        <div class="period-details">
                             <div class="months-container" style="display: flex; flex-direction: column; gap: 15px;">
                                @foreach ($period->meses_calculados as $mes)
                                    @php
                                        $facturasDelMes = $allBillingsForPeriod->filter(function($b) use ($mes) {
                                            return \Carbon\Carbon::parse($b->fecha_vencimiento)->format('Y-m') === $mes['key'];
                                        });
                                    @endphp
                                    <div class="monthly-block">
                                        
                                        {{-- CAMBIO: Encabezado del mes color Azul y texto blanco --}}
                                        <div class="month-header">
                                            <span>{{ $mes['label'] }}</span>
                                            @if($facturasDelMes->isNotEmpty())
                                                <span style="font-size: 0.8rem; color: #223F70; font-weight: 600; background: #fff; padding: 2px 8px; border-radius: 10px;">✓ Disponible</span>
                                            @else
                                                <span style="font-size: 0.8rem; color: #ccc; font-weight: normal;">-</span>
                                            @endif
                                        </div>
                                        
                                        @if($facturasDelMes->isNotEmpty())
                                            {{-- TABLA DE FACTURAS DEL ALUMNO --}}
                                            <div class="table-container">
                                                <table class="main-table">
                                                    <thead> 
                                                        {{-- CAMBIO: Cabecera de tabla color Azul y texto blanco --}}
                                                        <tr class="table-header--blue"> 
                                                            <th width="25%">Concepto</th> 
                                                            <th width="15%">Monto</th> 
                                                            <th width="15%">Vencimiento</th> 
                                                            <th width="15%">Status</th> 
                                                            <th width="15%">Acciones</th> 
                                                        </tr> 
                                                    </thead>
                                                    <tbody class="billing-item-tbody">
                                                        @foreach ($facturasDelMes as $billing)
                                                            @php
                                                                $estatus = $billing->computed_status;
                                                                $totalPagado = $billing->payments->sum('monto');
                                                                $saldo = $billing->monto - $totalPagado;
                                                                $estatusIcono = match($estatus) { 'Pagada' => 'circulo_verde.png', 'Abonado' => 'circulo_amarillo.png', default => 'circulo_rojo.png' };
                                                                $colorStatus = match($estatus) { 'Pagada' => '#28a745', 'Abonado' => '#ffc107', default => '#dc3545' };
                                                            @endphp
                                                            <tr class="billing-main-row">
                                                                <td>{{ $billing->concepto }}</td>
                                                                <td>
                                                                    ${{ number_format($billing->monto, 2) }}
                                                                    @if($estatus == 'Abonado') <br><small style="color:#e8a800">Saldo: ${{number_format($saldo,2)}}</small> @endif
                                                                </td>
                                                                <td>{{ \Carbon\Carbon::parse($billing->fecha_vencimiento)->format('d/m/Y') }}</td>
                                                                <td style="font-weight:500;">
                                                                    <div class="estado">
                                                                        {{-- AQUÍ SE AGREGÓ oncontextmenu="return false;" --}}
                                                                        <img src="{{ asset('images/icons/'.$estatusIcono) }}" alt="{{$estatus}}" class="estado-icono" draggable="false" oncontextmenu="return false;">
                                                                        <span style="color: {{ $colorStatus }}">{{ $estatus }}</span>
                                                                    </div>
                                                                </td>
                                                                <td class="acciones" style="padding:10px;">
                                                                    <svg class="icon icon-toggle" title="Ver Historial" style="cursor:pointer; width:20px; height:20px; vertical-align:middle;" viewBox="0 0 24 24" fill="#223F70" xmlns="http://www.w3.org/2000/svg" oncontextmenu="return false;"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                                                                    @if($billing->archivo_path)<a href="{{ Storage::url($billing->archivo_path) }}" target="_blank"><img src="{{ asset('images/icons/pdf.png') }}" class="icon" draggable="false" oncontextmenu="return false;"></a>@endif
                                                                    @if($billing->xml_path)<a href="{{ Storage::url($billing->xml_path) }}" target="_blank"><img src="{{ asset('images/icons/xml.png') }}" class="icon" draggable="false" oncontextmenu="return false;"></a>@endif
                                                                </td>
                                                            </tr>
                                                            {{-- FILA HISTORIAL ALUMNO (SOLO LECTURA) --}}
                                                            <tr class="payment-details-row">
                                                                <td colspan="6">
                                                                    @if($billing->cargo_moratorio_aplicado)
                                                                    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 12px 15px; margin-bottom: 10px;">
                                                                        <h4 style="margin:0 0 8px; color:#856404;">⚠️ Cargo Moratorio Aplicado</h4>
                                                                        <p style="margin:2px 0; font-size:0.9em; color:#856404;">
                                                                            <strong>Porcentaje:</strong> {{ $billing->porcentaje_cargo_moratorio }}% |
                                                                            <strong>Cargo:</strong> ${{ number_format($billing->cargo_monetario, 2) }} |
                                                                            <strong>Fecha aplicación:</strong> {{ \Carbon\Carbon::parse($billing->fecha_cargo_moratorio)->format('d/m/Y') }}
                                                                        </p>
                                                                        @if($billing->fecha_prorroga_fin)
                                                                        <p style="margin:2px 0; font-size:0.9em; color:#856404;">
                                                                            <strong>Prórroga hasta:</strong> {{ \Carbon\Carbon::parse($billing->fecha_prorroga_fin)->format('d/m/Y') }}
                                                                            @php $diasProrrogaRestantes = \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($billing->fecha_prorroga_fin), false); @endphp
                                                                            @if($diasProrrogaRestantes > 0)
                                                                                <span style="color:#dc3545; font-weight:bold;"> ({{ $diasProrrogaRestantes }} día(s) restantes)</span>
                                                                            @elseif($diasProrrogaRestantes == 0)
                                                                                <span style="color:#dc3545; font-weight:bold;"> (Vence HOY)</span>
                                                                            @else
                                                                                <span style="color:#dc3545; font-weight:bold;"> (Expirada)</span>
                                                                            @endif
                                                                        </p>
                                                                        @endif
                                                                    </div>
                                                                    @endif
                                                                    <div class="payment-history">
                                                                        <h4 style="margin:0 0 10px; color:#223F70;">Historial de Abonos</h4>
                                                                        @if($billing->payments->isNotEmpty())
                                                                        <ul> @foreach($billing->payments as $payment) <li> <span class="payment-date">{{ \Carbon\Carbon::parse($payment->fecha_pago)->format('d/m/Y') }} - {{ $payment->nota ?? 'Abono' }}</span> <span class="payment-amount">- ${{ number_format($payment->monto, 2) }}</span> </li> @endforeach </ul>
                                                                        @else <p>No hay pagos registrados.</p> @endif
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                             </div>
                        </div>
                    </details>
                 @endforeach
            </div>
        @else
            <p style="text-align: center; color: #888; padding: 20px;">No hay datos de facturación disponibles.</p>
        @endif
    </div>
@if(Auth::check() && (Auth::user()->hasActiveRole('master') || Auth::user()->hasActiveRole('control_administrativo')))
    
    <div id="modalFactura" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            
            {{-- TÍTULO OPTIMIZADO: La estructura y el salto de línea (<br>) ya están aquí. 
                 El JS solo rellenará el span #modalUserName. --}}
            <h2 id="modalTitle" class="modal-title">
                Agregar Factura a:<br>
                <span id="modalUserName" style="display:block; margin-top:5px; font-weight:700; font-size: 0.9em;"></span>
            </h2>
            
           <form id="formFacturaModal" method="POST" action="{{ route('Facturacion.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="modal_user_id" name="user_id" value="">
            <input type="hidden" id="modal_uid_prefix" name="uid_prefix" value="EXT-">

                {{-- 1. Período Activo --}}
                <label for="modal_period_id" style="font-weight:bold; display:block; margin-top:10px; text-align:left;">Período Activo:</label>
                <select id="modal_period_id" name="period_id" required class="filter-select" style="width:100%; background-color: #e9ecef; pointer-events: none;" readonly tabindex="-1">
                    @foreach ($periods as $period)
                        @if($period->is_active == 1)
                            <option value="{{ $period->id }}" selected>{{ $period->name }}</option>
                        @endif
                    @endforeach
                </select>

                {{-- 2. Concepto: catálogo (MEN-) vs texto libre (EXT-) — controlado por JS --}}
                <span id="modal_concepto_label" style="font-weight:bold; display:block; margin-top:10px; text-align:left;">Concepto:</span>
                <div id="modal_concepto_men_wrap">
                    <select id="modal_concepto" name="concepto" required class="filter-select" style="width: 100%; padding: 8px;">
                        <option value="" data-amount="">   Seleccione un concepto   </option>
                        @if(isset($conceptosDisponibles))
                            @foreach($conceptosDisponibles as $c)
                                <option value="{{ $c->concept }}"
                                        data-amount="{{ $c->amount }}"
                                        data-porcentaje-cargo-moratorio="{{ $c->porcentaje_cargo_moratorio }}"
                                        data-cargo-monetario="{{ $c->cargo_monetario }}"
                                        data-fecha-vencimiento-moratorio="{{ $c->fecha_vencimiento_moratorio ? \Carbon\Carbon::parse($c->fecha_vencimiento_moratorio)->format('Y-m-d') : '' }}">
                                    {{ $c->concept }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div id="modal_concepto_ext_wrap" style="display: none;">
                    <input type="text"
                           id="modal_concepto_ext"
                           maxlength="255"
                           placeholder="Escriba el concepto"
                           autocomplete="off"
                           readonly
                           style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                </div>

                {{-- 3. Monto: solo lectura desde catálogo (MEN-) vs captura manual (EXT-) --}}
                <label id="modal_monto_label" for="modal_monto_visible" style="font-weight:bold; display:block; margin-top:10px; text-align:left;">Monto de tiempo normal:</label>
                <div id="modal_monto_men_wrap">
                    <input type="text"
                           id="modal_monto_visible"
                           readonly
                           placeholder="$ 0.00"
                           style="width: 100%; padding: 10px; background-color: #f8f9fa; border: 1px solid #ccc; border-radius: 4px; font-weight: bold; color: #333; transition: background-color 0.3s;">
                    <input type="hidden" id="modal_monto" name="monto" required>
                </div>
                <div id="modal_monto_ext_wrap" style="display: none;">
                    <input type="number"
                           id="modal_monto_ext"
                           step="0.01"
                           min="0"
                           placeholder="0.00"
                           readonly
                           style="width: 100%; padding: 10px; background-color: #f8f9fa; border: 1px solid #ccc; border-radius: 4px; font-weight: bold; color: #333; box-sizing: border-box;">
                </div>

                {{-- Solo factura extra (EXT-). Mensualidad (MEN-): oculto y no se envía --}}
                <div id="modal_factura_extra_only">
                <label for="modal_porcentaje_cargo_moratorio" style="font-weight:bold; display:block; margin-top:10px; text-align:left;">Porcentaje de cargo moratorio:</label>
                <input type="text"
                       id="modal_porcentaje_cargo_moratorio"
                       name="porcentaje_cargo_moratorio"
                       inputmode="decimal"
                       autocomplete="off"
                       placeholder="0"
                       readonly
                       style="width: 100%; padding: 10px; background-color: #f8f9fa; border: 1px solid #ccc; border-radius: 4px; color: #333; box-sizing: border-box;">

                <label for="modal_cargo_monetario" style="font-weight:bold; display:block; margin-top:10px; text-align:left;">Cargo monetario:</label>
                <input type="text"
                       id="modal_cargo_monetario"
                       name="cargo_monetario"
                       inputmode="decimal"
                       autocomplete="off"
                       readonly
                       placeholder="0.00"
                       title="Calculado: monto × (porcentaje ÷ 100)"
                       style="width: 100%; padding: 10px; background-color: #f8f9fa; border: 1px solid #ccc; border-radius: 4px; color: #333; box-sizing: border-box;">
                </div>

                {{-- 4. Fecha Vencimiento (valor enviado en el formulario; EXT- editable, MEN- solo lectura vía JS) --}}
                <label for="modal_fecha" style="font-weight:bold; display:block; margin-top:10px; text-align:left;">Fecha vencimiento (asignada por sistema):</label>
                <input type="date"
                       id="modal_fecha"
                       name="fecha"
                       required
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-bottom: 15px;">

                {{-- 5. Estado --}}
                <label for="modal_status" style="font-weight:bold; display:block; margin-top:10px; text-align:left;">Estado:</label>
                <select id="modal_status" name="status" required style="width: 100%; padding: 8px; margin-bottom: 20px;">
                    <option value="Pendiente">Pendiente</option>
                    <option value="Pagada">Pagada</option>
                </select>

                {{-- 6. Archivos (OPCIONALES) --}}
                <label for="modal_archivo_pdf" style="font-weight:bold; display:block; margin-top:10px; text-align:left;">Archivo (PDF) (Opcional):</label>
                <input type="file" id="modal_archivo_pdf" name="archivo" accept=".pdf" style="width: 100%;">
                <small style="color: #666;">Solo archivos .pdf</small>

                <label for="modal_archivo_xml" style="font-weight:bold; display:block; margin-top:10px; text-align:left;">Subir XML (Opcional):</label>
                <input type="file" id="modal_archivo_xml" name="archivo_xml" accept=".xml,text/xml" style="width: 100%;">
                <small style="color: #666;">Solo archivos .xml</small>



                <button type="submit" class="guardar">Guardar Factura</button>
            </form>
        </div>
    </div>
@endif

<div id="billing-alerts-data" 
     data-alerts="{{ json_encode($alertasVencimiento ?? []) }}" 
     style="display: none;">
</div>

@endsection