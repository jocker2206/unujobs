<?php
/**
 * Http/Controllers/ConvocatoriaController.php
 * 
 * @author Hans <twd2206@gmail.com>
 */
namespace App\Http\Controllers;

use App\Models\Convocatoria;
use Illuminate\Http\Request;
use App\Http\Requests\ConvocatoriaRequest;
use App\Models\Personal;
use App\Http\Requests\ConvocatoriaUpdateRequest;
use App\Models\Actividad;
use App\Models\TypeEtapa;
use App\Models\Postulante;
use App\Models\Etapa;
use \DB;
use \PDF;

/**
 * Class ConvocatoriaController
 * 
 * @category Controllers
 */
class ConvocatoriaController extends Controller
{


    public function __construct()
    {

        $this->middleware('origen')->only('index');

    }

    /**
     * Muestra una lista de convocatorias
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $convocatorias = Convocatoria::orderBy("id", 'DESC')->paginate(20);
        return view('convocatoria.index', compact('convocatorias'));
    }

    /**
     * Muestra un formulario para crear una nueva convocatoria
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $personals = Personal::where("aceptado", 1)
            ->where("fecha_final", ">=", date('Y-m-d'))
            ->get();
            
        return view("convocatoria.create", \compact('personals'));
    }

    
    /**
     * Almacena una convocatoria recien creada
     *
     * @param  \App\Http\Request\ConvocatoriaRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ConvocatoriaRequest $request)
    {   
        $convocatoria = Convocatoria::create($request->except('aceptado'));
        return back()->with(["success" => "Los datos se guardaron correctamente!"]);
    }

    /**
     * Undocumented function
     *
     * @param Convocatoria $convocatoria
     * @return void
     */
    public function show(Convocatoria $convocatoria)
    {
        return back();
    }


    /**
     * Muestra un formulario para editar una convocatoria
     *
     * @param  string  $slug
     * @return \Illuminate\View\View
     */
    public function edit($slug)
    {
        $id =  \base64_decode($slug);
        
        $actividades = [];

        $convocatoria = Convocatoria::findOrFail($id);
        $personals = Personal::where("aceptado", 1)
            ->where("fecha_final", ">=", date('Y-m-d'))
            ->whereIn("convocatoria_id", [0, $convocatoria->id])
            ->orWhere("convocatoria_id", null)
            ->get();

        foreach ($convocatoria->actividades as $actividad) {
            array_push($actividades, [
                $actividad->id, 
                $actividad->descripcion, 
                $actividad->fecha_inicio, 
                $actividad->fecha_final,
                $actividad->responsable
            ]);
        }

        return view('convocatoria.edit', \compact('convocatoria', 'personals', 'actividades'));
    }


    /**
     * Actualiza una convocatoria recien modificada
     *
     * @param  \App\Http\Requests\ConvocatoriaUpdateRequest  $request
     * @param  int  $slug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ConvocatoriaUpdateRequest $request, $slug)
    {
        $id = \base64_decode($slug);

        $convocatoria = Convocatoria::findOrFail($id);   
        $convocatoria->update($request->all());
        $whereNoIn = [];

        foreach ($convocatoria->personals as $per) {
            $per->update(["convocatoria_id" =>  0]);
        }

        $personals = $request->input('personals', []);

        //configurar personals
        foreach ($personals as $per) {
            $personal = Personal::find($per);
            if ($personal) {
                $personal->update(["convocatoria_id" => $convocatoria->id]);
            }
        }

        $activities = $request->input('activities', []);

        //configurar activities
        foreach ($activities as $activity) {
            $tmpID = isset($activity[0]) ? $activity[0] : "";
            $descripcion = isset($activity[1]) ? $activity[1] : "";
            $f_inicio = isset($activity[2]) ? $activity[2] : null;
            $f_final = isset($activity[3]) ? $activity[3] : null;
            $responsable = isset($activity[4]) ? $activity[4] : "";

            $actividad = Actividad::find($tmpID);

            if ($actividad) {
                $actividad->update([
                    "descripcion" => $descripcion,
                    "fecha_inicio" => $f_inicio,
                    "fecha_final" => $f_final,
                    "responsable" => $responsable,
                    "convocatoria_id" => $convocatoria->id
                ]);
            }else {
                $actividad = Actividad::create([
                    "descripcion" => $descripcion,
                    "fecha_inicio" => $f_inicio,
                    "fecha_final" => $f_final,
                    "responsable" => $responsable,
                    "convocatoria_id" => $convocatoria->id
                ]);
            }

            
            array_push($whereNoIn, $actividad->id);

        }


        //eliminar las actividades que ya no estan en el request
        DB::table("actividads")->whereNotIn("id", $whereNoIn)->delete();

        
        return back()->with(["success" => "Los datos se actualizarón correctamente"]);
    }


    /**
     * Undocumented function
     *
     * @param Convocatoria $convocatoria
     * @return void
     */
    public function destroy(Convocatoria $convocatoria)
    {
        return back();
    }


    /**
     * Actualiza el estado de la convocatoria  "aceptado" o "rechazado"
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\View\View  
     */
    public function aceptar(Request $request, $id)
    {
        $convocatoria = Convocatoria::findOrFail($id);
        $convocatoria->update([
            "aceptado" => $convocatoria->aceptado ? 0 : 1
        ]);

        return back();
    }

    /**
     * Genera un archivo PDF de la convocatoria
     *
     * @param  string  $slug
     * @return \PDF
     */
    public function pdf($slug)
    {
        $id = \base64_decode($slug);

        $convocatoria = Convocatoria::findOrFail($id);
        $year = isset(explode("-", $convocatoria->fecha_inicio)[0]) ? explode("-", $convocatoria->fecha_inicio)[0] : date('Y');
        $pdf = PDF::loadView('pdf.convocatoria', compact('convocatoria', 'year'));
        return $pdf->stream();
    }


    /**
     * Muestra una convocatoria con sus respectivos requerimientos de personal y etapas
     *
     * @param  string  $slug
     * @return \Illuminate\View\View
     */
    public function etapas($slug)
    {

        $id = \base64_decode($slug);

        $convocatoria = Convocatoria::findOrFail($id);
        $year = isset(explode("-", $convocatoria->fecha_final)[0]) ? explode("-", $convocatoria->fecha_final)[0] : date('Y');
        $etapas = TypeEtapa::all();

        $current = request()->personal 
            ?   $convocatoria->personals->where("slug", request()->personal)->first() 
            :   $convocatoria->personals->first();

        if ($current) {

            foreach ($etapas as $etapa) {
                $tmp_postulantes = Postulante::whereHas("etapas", function($e) use($current){
                            $e->where("personal_id", $current->id);
                            $e->where("convocatoria_id", $current->convocatoria_id);
                        })->whereHas("etapas", function($e) use($etapa) {
                            $e->where("type_etapa_id", $etapa->id);
                        })->get(); 
    
                foreach ($tmp_postulantes as $postulante) {
                    $tmp_etapa = Etapa::where("postulante_id", $postulante->id)
                        ->where("type_etapa_id", $etapa->id)
                        ->where("convocatoria_id", $current->convocatoria_id)
                        ->where("personal_id", $current->id)
                        ->first();

                    $postulante->next = $tmp_etapa ? $tmp_etapa->next : 0;
                    $postulante->puntaje = $tmp_etapa ? $tmp_etapa->puntaje : 0;
    
                }
                
                $etapa->postulantes = $tmp_postulantes;
                
            }
        } else {
            $etapas = $etapas->map(function($e) {
                $e->postulantes = collect();
                return $e;
            });
        }

        $hasExpire = $convocatoria->fecha_final < date('Y-m-d') ? true : false;

        return view("convocatoria.etapas", compact('convocatoria', 'etapas', 'year', 'current', 'hasExpire'));
    }
}
