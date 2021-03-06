@extends('layouts.app')

@section('titulo')
    - Cronogramas
@endsection

@section('link')
    Planilla
@endsection

@section('content')


<div class="col-md-12 mb-2">
    <a href="{{ route('cronograma.index', ["mes={$cronograma->mes}&year={$cronograma->año}"]) }}" 
        class="btn btn-danger"
    >
        <i class="fas fa-arrow-left"></i> atrás
    </a>

    @if ($cronograma->adicional)
        <add-work
            theme="btn-primary"
            class="text-left"
            :cronograma="{{ $cronograma }}"
        >
            Agregar Tragajadores
        </add-work>
    @endif
    <btn-reporte theme="btn-primary"
        :cronograma="{{ $cronograma }}"
        :type_reports="{{ $typeReports }}"
        :metas="{{ $metas }}"
    >
        <i class="fas fa-file-pdf"></i> Reportes
    </btn-reporte>

    <delete-work
        theme="btn-danger"
        class="text-left"
        :cronograma="{{ $cronograma }}"
    >
        Eliminar Trabajador
    </delete-work>

</div>


<div class="row">
    <h3 class="ml-2 col-md-10 uppercase">
        @if ($cronograma->adicional)
            Adicional 
            <span class="btn btn-sm btn-primary">{{ $cronograma->numero }}</span>
            <span class="text-danger"> >> </span>
        @endif
        {{ $cronograma->planilla ? $cronograma->planilla->descripcion : null }} 
        del {{ $cronograma->mes }} - {{ $cronograma->año }}
    </h3>
    <h2 class="text-right">
        <i class="fas fa-users fa-sm text-primary"></i>
        {!! isset($historial) == 1 ? $historial->total() : 0 !!}
    </h2>
</div>


@if (session('success'))
    <div class="col-md-12 mt-3 ">
        <div class="alert alert-success">
            {{ session('success') }}       
        </div>
    </div>
@endif


@if (session('danger'))
    <div class="col-md-12 mt-3 ">
        <div class="alert alert-danger">
            {{ session('danger') }}       
        </div>
    </div>
@endif


@if ($errors->first('import_remuneracion'))
    <div class="col-md-12 mt-3 ">
        <div class="alert alert-danger">
            {{ $errors->first('import_remuneracion') }}       
        </div>
    </div>
@endif


@if ($errors->first('import_descuento'))
    <div class="col-md-12 mt-3 ">
        <div class="alert alert-danger">
            {{ $errors->first('import_descuento') }}       
        </div>
    </div>
@endif


<div class="col-md-12">

    @if ($historial)
        {{ $historial->appends([
            "meta_id" => $meta_id,
            "cargo_id" => $cargo_id
        ])->links() }}
    @endif

    <div class="card">
        <div class="card-header card-header-danger">
            <h4 class="card-title">Lista de Trabajadores</h4>
            <p class="card-category">Que pertenecen a esta planilla</p>
        </div>
        <div class="card-body">
            <div class="table-responsive">

                <form class="col-md-12 mb-3" method="GET">
                    <div class="row">
                        <div class="col-md-2">
                            <select name="meta_id" class="form-control">
                                <option value="">Metas</option>
                                @foreach ($metas as $meta)
                                    <option value="{{ $meta->id }}" {!! $meta_id == $meta->id ? 'selected' : '' !!}>{{ $meta->metaID }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="cargo_id" class="form-control">
                                <option value="">Cargos</option>
                                @foreach ($cargos as $cargo)
                                    <option value="{{ $cargo->id }}" {!! $cargo_id == $cargo->id ? 'selected' : '' !!}>{{ $cargo->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            Plaza
                            <input type="checkbox"  {!! request()->plaza ? 'checked' : null !!} name="plaza">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="query_search" value="{{ $like }}">
                        </div>
        
                        <div class="col-md-1">
                            <button class="btn btn-info">
                                <i class="fas fa-search"></i> 
                            </button>
                        </div>    

                        <div class="col-md-3">

                            <div class="row">

                                <import id="import-remuneracion"
                                    class="ml-1"
                                    formato="{{ url('/formatos/remuneracion_import.xlsx') }}"
                                    url="/remuneracion/{{ $cronograma->slug() }}"
                                    formulario="form-import-remuneracion"
                                    param="{{ auth()->user()->id }}"
                                >
                                    <i class="fas fa-file-excel"></i> Imp. Rem
                                </import>

                                <import id="import-descuento"
                                    class="ml-1"
                                    formato="{{ url('/formatos/descuento_import.xlsx') }}"
                                    url="/descuento/{{ $cronograma->slug() }}"
                                    formulario="form-import-descuento"
                                    param="{{ auth()->user()->id }}"
                                >
                                    <i class="fas fa-file-excel"></i> Imp. Desc
                                </import>

                            </div>

                        </div>
                    </div>
                </form>


                <table class="table">
                    <thead class="text-primary">
                        <tr>
                            <th>#ID</th>
                            <th>Nombre Completo</th>
                            <th>N° Documento</th>
                            <th>Categorias</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($historial as $history)
                            @php
                                $job = $history->work;
                            @endphp
                            <tr>
                                <th>{{ $history->id }}</th>
                                <th class="uppercase">{{ $job->nombre_completo }}</th>
                                <th>{{ $job->numero_de_documento }}</th>
                                <th class="uppercase">
                                    <div class="btn btn-sm btn-danger">
                                        {{ $history->categoria ? $history->categoria->nombre : '' }}
                                    </div>
                                </th>
                                <th>
                                    <div class="btn-group">

                                        <btn-boleta theme="btn-danger btn-sm"
                                            param="{{ $history->info_id }}"
                                            url="{{ route('job.boleta.store', $job->id) }}"
                                            nombre_completo="{{ $job->nombre_completo }}"
                                            token="{{ csrf_token() }}"
                                        >
                                            <i class="fas fa-file-alt"></i>
                                        </btn-boleta>

                                        <btn-detalle theme="btn-warning btn-sm"
                                            param="{{ $history->id }}"
                                            nombre_completo="{{ $job->nombre_completo }}"
                                            :paginate="{{ $indices }}"
                                            :historial="{{ $history }}"
                                            :cronograma="{{ $cronograma }}"
                                        >
                                            <i class="fas fa-wallet"></i>
                                        </btn-detalle>

                                        <btn-work-config theme="btn-dark btn-sm"
                                            param="{{ $job->id }}"
                                            nombre_completo="{{ $job->nombre_completo }}"
                                        >
                                            <i class="fas fa-cog"></i>
                                        </btn-work-config>
                                        
                                    </div>
                                </th>
                            </tr>
                        @empty
                            <tr>
                                <th colspan="5" class="text-center">No hay registros disponibles</th>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>        

    @php
        $estado = $cronograma->estado ? 1 : 0;
    @endphp

    <switch-planilla 
        :active="{{ $estado }}"
        :param="'{{ $cronograma->id }}'"
        :user_id="'{{ auth()->user()->id }}'"
    >
    </switch-planilla>

</div>

@endsection