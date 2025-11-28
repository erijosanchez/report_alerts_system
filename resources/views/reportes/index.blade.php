@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
    <!-- SELECTOR DE REPORTE Y FECHAS -->
    <div class="mb-4 card">
        <div class="card-body">
            <form method="GET" action="{{ route('reportes.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Reporte</label>
                        <select name="tipo" class="form-select" onchange="this.form.submit()">
                            <option value="general" {{ $tipoReporte == 'general' ? 'selected' : '' }}>General</option>
                            <option value="tickets" {{ $tipoReporte == 'tickets' ? 'selected' : '' }}>Tickets</option>
                            <option value="monitoreo" {{ $tipoReporte == 'monitoreo' ? 'selected' : '' }}>Monitoreo</option>
                            <option value="alarmas" {{ $tipoReporte == 'alarmas' ? 'selected' : '' }}>Alarmas</option>
                            <option value="tecnicos" {{ $tipoReporte == 'tecnicos' ? 'selected' : '' }}>Técnicos</option>
                            <option value="sla" {{ $tipoReporte == 'sla' ? 'selected' : '' }}>SLA</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Desde</label>
                        <input type="date" name="desde" class="form-control" value="{{ $desde }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="hasta" class="form-control" value="{{ $hasta }}">
                    </div>
                    <div class="d-flex align-items-end col-md-3">
                        <button type="submit" class="w-100 btn btn-primary">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($tipoReporte == 'general')
        @include('reportes.partials.general', ['data' => $data])
    @elseif($tipoReporte == 'tickets')
        @include('reportes.partials.tickets', ['data' => $data])
    @elseif($tipoReporte == 'monitoreo')
        @include('reportes.partials.monitoreo', ['data' => $data])
    @elseif($tipoReporte == 'alarmas')
        @include('reportes.partials.alarmas', ['data' => $data])
    @elseif($tipoReporte == 'tecnicos')
        @include('reportes.partials.tecnicos', ['data' => $data])
    @elseif($tipoReporte == 'sla')
        @include('reportes.partials.sla', ['data' => $data])
    @endif
@endsection
