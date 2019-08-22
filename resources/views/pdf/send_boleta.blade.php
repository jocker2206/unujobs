<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="{{ public_path() . "/css/app.css" }}">
        <link rel="stylesheet" href="{{ public_path() . "/css/pdf.css" }}">
        <title>Reporte Mesual de boleta - {{ $cronograma->mes }}-{{ $cronograma->year }}</title>
    </head>

    @foreach ($work->infos as $info)
    <body class="bg-white text-dark">

            <div class="mb-7">
                    <div class="text-center"></div>
                        
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <img src="{{ public_path() . "/img/logo.png" }}" width="50" alt="">
                                </th>
                                <th>
                                    <div><b>UNIVERSIDAD NACIONAL DE UCAYALI</b></div>
                                    <div class="ml-1 text-sm">OFICINA GENERAL DE RECURSOS HUMANOS</div>
                                    <div class="ml-1 text-sm">OFICINA EJECUTIVA DE REMUNERACIONES Y PENSIONES</div>
                                </th>
                            </tr>
                        </thead>
                    </table>
                
                    <h6 class="mt-1 text-center mb-2 uppercase"></h6>
                
                    <div class="text-md uppercase">
                        <b> 
                            Actividad: {{ $info->meta ? $info->meta->actividadID : null }} {{ $info->meta ? $info->meta->actividad : null }}
                        </b>
                    </div>
                    
                    <div class="boleta-header" style="width:90%;">
                        <table class="table-boleta table-sm" style="width:100%;">
                            <thead> 
                                <tr>
                                    <td class="py-0 pl-3 pt-1">Boleta de Pago N°:</td>
                                    <td class="py-0 pt-1">{{ $work->id }}</td>
                                    <td class="py-0 pt-1">Fecha de Ingreso:</td>
                                    <td class="py-0 pt-1">{{ date($work->fecha_de_ingreso) }}</td>
                                    <td class="py-0 pt-1" width="10%">D.N.I.</td>
                                    <td class="py-0 pt-1">{{ $work->numero_de_documento }}</td>
                                </tr>
                                <tr>
                                    <td class="py-0 pl-3">A.F.P:</td>
                                    <td class="py-0" colspan="3">{{ $work->afp ? $work->afp->desripcion: null }}</td>
                                    <td class="py-0" width="10%">N° CUSSP</td>
                                    <td class="py-0">{{ $work->numero_de_cussp }}</td>
                                </tr>
                                <tr>
                                    <td class="py-0 pl-3">Nombres y Apellidos</td>
                                    <td colspan="3" class="uppercase py-0">{{ $work->profesion }} {{ $work->nombre_completo }}</td>
                                    <td class="py-0" width="10%">N° ESSALUD</td>
                                    <td class="py-0">{{ $work->numero_de_essalud }}</td>
                                </tr>
                                <tr>
                                    <td class="py-0 pl-3">Condición Laboral</td>
                                    <td colspan="3" class="uppercase py-0">{{ $info->cargo ? $info->cargo->descripcion : null }} - {{ $info->cargo ? $info->cargo->tag : '' }}</td>
                                    <td class="py-0" width="10%">Meta Siaf:</td>
                                    <td class="py-0">{{ $info->meta ? $info->meta->metaID : null }}</td>
                                </tr>
                                <tr>
                                    <td class="py-0 pl-3 pb-1">Cargo</td>
                                    <td colspan="3" class="uppercase py-0 pb-1">{{ $info->perfil }}</td>
                                    <td class="py-0 pb-1" width="10%">Categoría</td>
                                    <td class="uppercase py-0 pb-1">{{ $info->categoria ? $info->categoria->nombre : null }}</td>
                                </tr>
                            </thead>
                        </table>
                    </div>
    
                    <table class="table-sm mt-2" style="width:90%;">
                        <thead class="py-0 bbt-1 bbl-1 bbb-1">
                            <tr class="text-center py-0">
                                <th class="py-0">
                                    <div class="py-0">
                                        INGRESOS
                                    </div>
                                </th>
                                <th class="py-0 bbl-1">
                                    <div class="py-0">
                                        RETENCIONES
                                    </div>
                                </th>
                                <th class="py-0 bbr-1 bbl-1">
                                    <div class="py-0">
                                        APORTES EMPLEADOR
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="bbr-1 p-relative">
                                    <table class="w-100">
                                        <tbody>
                                            @foreach ($info->remuneraciones as $remuneracion)
                                                <tr>
                                                    <td class="py-0">
                                                        {{ $remuneracion->typeRemuneracion ? $remuneracion->typeRemuneracion->key : null }}
                                                        <span>.-</span>
                                                        {{ $remuneracion->typeRemuneracion ? $remuneracion->typeRemuneracion->descripcion : null }}
                                                    </td>
                                                    <td class="py-0 text-right" width="5%">
                                                        {{ round($remuneracion->monto, 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tr>
                                                <td></td>
                                                <th class="text-right bbt-1">
                                                    {{ round($info->total, 2) }}
                                                </th>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td class="p-relative">
                                    <table class="p-absolute top-0 w-100">
                                        <tbody>
                                            @foreach ($info->descuentos as $body)
                                                <tr>
                                                    @foreach ($body as $descuento)
                                                        <td class="py-0" width="35%">
                                                            {{ $descuento['type_descuento'] ? $descuento['type_descuento']['key'] : null }}
                                                            <span>.-</span>
                                                            {{ $descuento['type_descuento'] ? $descuento['type_descuento']['descripcion'] : null }}
                                                        </td>
                                                        <td class="py-0 text-right">{{ round($descuento['monto'], 0) }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                            <tr>
                                                <th></th>
                                                <th colspan="3"></th>
                                            </tr>
                                            <tr>
                                                <th class="py-0">TOTAL DESCUENTOS</th>
                                                <th class="py-0 bbt-1 text-center" colspan="3">
                                                    {{ round($info->total_descuento, 2) }}
                                                </th>
                                            </tr>
                                            <tr>
                                                <th class="py-0">NETO A PAGAR</th>
                                                <th class="py-0 bbt-1 text-center" colspan="3">
                                                    {{ round($info->neto, 2) }}
                                                </th>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td class="bbl-1 p-relative">
                                    <table class="p-absolute top-0 w-100">
                                        
                                        @foreach ($info->aportaciones as $aporte)
                                            <tr>
                                                <td class="py-0">
                                                    {{ $aporte->typeDescuento ? $aporte->typeDescuento->key : '' }}
                                                    .-
                                                    {{ $aporte->typeDescuento ? $aporte->typeDescuento->descripcion : '' }}
                                                </td>
                                                <td class="py-0">{{ $aporte->monto }}</td>
                                            </tr>
                                        @endforeach
    
                                        <tr>
                                            <td></td>
                                            <td></td>
                                        </tr>
    
                                        <tr>
                                            <td class="py-0">90.-BASE IMPONIBLE</td>
                                            <td class="py-0">{{ $info->base }}</td>
                                        </tr>
                                        @for ($i = 0; $i < 15; $i++)
                                            <tr>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        @endfor
                                        <tr>
                                            <td colspan="2" class="py-0 p-absolute bottom-0 text-center">
                                                <div class="center">--------------------------------</div>
                                                <div class="center">RECIBI CONFORME</div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
            </body>
    @endforeach
</html>