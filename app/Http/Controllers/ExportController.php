<?php
/**
 * Http/Controllers/ExportController.php
 * 
 * @author Hans <twd2206@gmail.com>
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\WorkExport;
use App\Exports\MetaExport;
use App\Exports\CargoExport;
use App\Exports\CategoriaExport;
use App\Exports\CronogramaExport;
use App\Jobs\ExportQueue;
use App\Exports\AfpNet;
use App\Exports\TransparenciaExport;
use App\Exports\AltaBajaExport;
use App\Exports\ResumenExport;
use App\Models\Cronograma;
use App\Jobs\ReportCuenta;
use App\Jobs\ReportCheque;

/**
 * Class ExportController
 * 
 * @category Controllers
 */
class ExportController extends Controller
{
    
    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('validacion')->only(['work', 'cronograma']);
    }


    /**
     * Crea un archivo de excel de los trabajadores
     *
     * @param  \Illuminate\Http\Request  $request
     * @return  \Illuminate\Http\RedirectResponse
     */
    public function work(Request $request)
    {
        $this->validate(request(), [
            "limite" => "required|numeric"
        ]);

        $limite = round($request->limite, 0);
        $order = $request->order ? 'DESC' : 'ASC';
        $name = 'work_' . date('Y-m-d') . '.xlsx';
        $ruta = "public/excels/{$name}";
            
        (new WorkExport($limite, $order))->queue($ruta)->chain([
            new ExportQueue("/storage/excels/{$name}", $name)
        ]);

        return back()->with(["success" => "Le notificaremos cuando la exportación esté lista"]);
    }


    /**
     * Crea un archivo de excel de las metas presupuestales
     *
     * @param  \Illuminate\Http\Request  $request
     * @return  \Illuminate\Http\RedirectResponse
     */
    public function meta(Request $request)
    {
        $this->validate(request(), [
            "limite" => "required|numeric"
        ]);

        $limite = round($request->limite, 0);
        $order = $request->order ? 'DESC' : 'ASC';
        $name = 'meta_' . date('Y-m-d') . '.xlsx';
        $ruta = "public/excels/{$name}";
            
        (new MetaExport($limite, $order))->queue($ruta)->chain([
            new ExportQueue("/storage/excels/{$name}", $name)
        ]);

        return back()->with(["success" => "Le notificaremos cuando la exportación esté lista."]);
    }


    /**
     * Crea un archivo de excel de los cargos
     *
     * @return  \Illuminate\Http\RedirectResponse
     */
    public function cargo()
    {
        $name = 'cargo_' . date('Y-m-d') . '.xlsx';
        $ruta = "public/excels/{$name}";
            
        (new CargoExport)->queue($ruta)->chain([
            new ExportQueue("/storage/excels/{$name}", $name)
        ]);

        return back()->with(["success" => "Le notificaremos cuando la exportación esté lista."]);
    }


    /**
     * Crea un archivo de excel de las categorias
     *
     * @return  \Illuminate\Http\RedirectResponse
     */
    public function categoria()
    {
        $name = 'categoria_' . date('Y-m-d') . '.xlsx';
        $ruta = "public/excels/{$name}";
            
        (new CategoriaExport)->queue($ruta)->chain([
            new ExportQueue("/storage/excels/{$name}", $name)
        ]);

        return back()->with(["success" => "Le notificaremos cuando la exportación esté lista."]);
    }


    /**
     * Crea un archivo de excel de los cronogramas
     *
     * @param  \Illuminate\Http\Request
     * @param  string  $id   
     * @return  \Illuminate\Http\RedirectResponse
     */
    public function cronograma(Request $request, $id)
    {
        $name = 'cronograma_' . date('Y-m-d') . '.xlsx';
        $ruta = "public/excels/{$name}";
            
        (new CronogramaExport($id))->queue($ruta)->chain([
            new ExportQueue("/storage/excels/{$name}", $name)
        ]);

        return back()->with(["success" => "Le notificaremos cuando la exportación esté lista."]);
    }


    /**
     * Crea un archivo de excel para AFPNET y PDT-PLAME
     *
     * @param  string  $id   
     * @return  \Illuminate\Http\RedirectResponse
     */
    public function afp($id)
    {
        try {
         
            $cronograma = Cronograma::findOrFail($id);

            //ruta de apt-net
            $name_afp_net = "afp_net_{$cronograma->mes}_{$cronograma->año}.xlsx";
            $ruta_apt_net = "public/excels/{$name_afp_net}";
            // exportar afp-net
            (new AfpNet($cronograma))->queue($ruta_apt_net)->chain([
                (new ExportQueue("/storage/excels/{$name_afp_net}", $name_afp_net))->onQueue('medium')
            ])->onQueue('medium');

            return [
                "status" => true,
                "message" => "Las solicitud está siendo procesada. Nosotros le notificaremos cuando este lista.",
                "body" => $cronograma
            ];
            
        } catch (\Throwable $th) {
            
            \Log::info($th);

            return [
                "status" => false,
                "message" => "Ocurrió un error al procesar la operación",
                "body" => ""
            ]; 

        }
       
    }


    public function mef($year, $mes)
    {
        try {
            
            $name = "transparencia_{$year}_{$mes}.xlsx";
            $ruta = "public/excels/{$name}";

            (new TransparenciaExport($year, $mes))->queue($ruta)->chain([
                new ExportQueue("/storage/excels/{$name}", $name)
            ]);

            return [
                "status" => true,
                "message" => "Las solicitud está siendo procesada. Nosotros le notificaremos cuando este lista.",
                "body" => ""
            ];

        } catch (\Throwable $th) {
            
            \Log::info($th);

            return [
                "status" => false,
                "message" => "Ocurrió un error al procesar la operación",
                "body" => ""
            ]; 

        }

    }


    public function altaBaja($year, $mes)
    {

        try {
            
            $name = "alta_baja_{$year}_{$mes}.xlsx";
            $ruta = "public/excels/{$name}";

            (new AltaBajaExport($year, $mes))->queue($ruta)->chain([
                new ExportQueue("/storage/excels/{$name}", $name)
            ]);

            return [
                "status" => true,
                "message" => "Las solicitud está siendo procesada. Nosotros le notificaremos cuando este lista.",
                "body" => $name
            ];

        } catch (\Throwable $th) {
            
            \Log::info($th);

            return [
                "status" => false,
                "message" => "Ocurrió un error al procesar la operación",
                "body" => ""
            ]; 

        }

    }


    public function resumen($year, $mes)
    {

        try {
            
            $name = "resumen_{$year}_{$mes}.xlsx";
            $ruta = "public/excels/{$name}";

            (new ResumenExport($year, $mes))->queue($ruta)->chain([
                new ExportQueue("/storage/excels/{$name}", $name)
            ]);

            return [
                "status" => true,
                "message" => "Las solicitud está siendo procesada. Nosotros le notificaremos cuando este lista.",
                "body" => $name
            ];

        } catch (\Throwable $th) {
            
            \Log::info($th);

            return [
                "status" => false,
                "message" => "Ocurrió un error al procesar la operación",
                "body" => ""
            ]; 

        }
    }

}
