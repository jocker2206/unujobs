<?php
/**
 * ./app/Http/Controllers/AfpController.php
 * 
 * @author Hans Medina <twd2206@gmail.com>
 */
namespace App\Http\Controllers;

use App\Models\Afp;
use Illuminate\Http\Request;
use App\Jobs\ReportAFP;
use App\Models\Cronograma;

/**
 * Controla las acciones en al ruta afp
 * 
 * @category Controllers
 */
class AfpController extends Controller
{

    /**
     * Muetra una lista de las recursos
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $afps = Afp::with('type_afps')->get();

        if (request()->ajax()) {
            return $afps;
        }

        return view('afps.index', compact('afps'));
        
    }

    
    /**
     * Muesta un formulario para crear un nuevo recurso
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('afps.create');
    }

    /**
     * Almacena un recurso recién creado en el almacenaciento
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            "nombre" => "required|unique:afps",
            "aporte" => "required|numeric",
            "prima" => "required|numeric"
        ]);

        $afp = Afp::create($request->all());

        return back()->with(["success" => "Los datos se guardarón correctamente"]);
    }

    /**
     * Muestra un recurso específico
     *
     * @param  \App\Models\Afp $actividad
     * @return \Illuminate\Http\Response
     */
    public function show(Afp $afp)
    {
        return back();
    }


    /**
     * Muestra un formulario para editar un recurso específico
     *
     * @param  \App\Models\Afp  $actividad
     * @return \Illuminate\View\View
     */
    public function edit($slug)
    {
        $id = \base64_decode($slug);
        $afp = Afp::findOrFail($id);

        return view('afps.edit', compact('afp'));
    }


    /**
     * Actualiza el recurso especificado en el almacenamiento.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Request  $id
     * @return \Illuminate\View\View
     */
    public function update(Request $request, $id)
    {
        $this->validate(request(), [
            "nombre" => "required",
            "aporte" => "required|numeric",
            "prima" => "required|numeric"
        ]);

        $afp = Afp::findOrFail($id);
        $afp->update($request->all());

        return back();
    }

    /**
     * No hace nada :)
     *
     * @param  \App\Models\Afp  $afp
     * @return \Illuminate\Http\Response
     */
    public function destroy(Afp $afp)
    {
        return back();
    }


    public function pdf(Request $request, $id) {
        
        $afp = Afp::findOrFail($id);
        $this->validate(request(), ["cronograma_id" => "required", "type_report" => "required"]);
        $cronograma = Cronograma::findOrFail($request->cronograma_id);

        try {

            ReportAFP::dispatch($cronograma, $afp, $request->type_report)->onQueue('medium');
            
            return [
                "status" => true,
                "message" => "El reporte está siendo generado, vuelva más tarde!"
            ];

        } catch (\Throwable $th) {
            
            \Log::info($th);

            return [
                "status" => false,
                "message" => "Algo salió mal!"
            ];

        }

    }

}
