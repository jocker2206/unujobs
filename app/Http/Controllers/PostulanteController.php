<?php
/**
 * Http/Controllers/PostulanteController.php
 * 
 * @author Hans Medina <twd2206@gmail.com>
 */
namespace App\Http\Controllers;

use App\Models\Postulante;
use Illuminate\Http\Request;
use App\Tools\Reniec;
use App\Http\Requests\PostulanteRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\Etapa;
use App\Models\Personal;

/**
 * Class PostulanteController
 * 
 * @category Controllers
 */
class PostulanteController extends Controller
{

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        return back();
    }

    
    /**
     * Muestra un formulario para crear un nuevo postulante
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $documento = request()->input("documento", null);
        $exists = false;
        $result = (object)["success" => false, "message" => ""];

        if($documento) {
            $reniect = new Reniec();
            $result = $reniect->search($documento);
        }

        return view("postulante.create", \compact('exists', 'result'));
    }

    /**
     * Almacena un postulante recien creado
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PostulanteRequest $request)
    {
        $postulante = Postulante::where("numero_de_documento", $request->numero_de_documento)->first();

        if($postulante) {
            $postulante->update($request->all());
        }else {
            $postulante = Postulante::create($request->all());
        }

        $nombre = "{$postulante->ape_paterno} {$postulante->ape_materno} {$postulante->nombres}";
        $postulante->update(["nombre_completo" => $nombre]);

        $personal = Personal::findOrfail($request->personal_id);

        $etapa = Etapa::updateOrCreate([
            "postulante_id" => $postulante->id,
            "type_etapa_id" => 1,
            "convocatoria_id" => $personal->convocatoria_id,
            "personal_id" => $personal->id,
            "current" => 1,
            "next" => 0
        ]);

        if ($request->redirect) {
            $id = \base64_encode($postulante->id);
            $guard = Cache::put('postulante', $id, 60);
            return \redirect($request->redirect . "?postulante={$id}")->with(["success" => "Los datos se guardaron correctamente!"]);
        }

        return back()->with(["success" => "Los datos se guardaron correctamente!"]);
    }

    /**
     * Undocumented function
     *
     * @param Postulante $postulante
     * @return void
     */
    public function show(Postulante $postulante)
    {
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Postulante  $postulante
     * @return \Illuminate\Http\Response
     */
    public function edit(Postulante $postulante)
    {
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Postulante  $postulante
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Postulante $postulante)
    {
        return back();
    }

    /**
     * Uncommented
     * 
     * @param  \App\Postulante  $postulante
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Postulante $postulante)
    {
        return  back();
    }

    /**
     * Sube el postulante su curriculum-vitae
     *
     * @param  string  $slug
     * @return \Illuminate\View\View
     */
    public function upload(Request $request, $id)
    {
        $this->validate(request(), [
            "cv" => "required|mimes:pdf"
        ]);

        $postulante = Postulante::findOrFail($id);
        $file = $request->file('cv');
        $name = "cv_{$postulante->id}.pdf";
        $store = Storage::disk('public')->putFileAs("pdf/cv", $file, $name);
        $postulante->update(["cv" => "storage/" . $store]);

        return back()->with(["cv" => "EL  CV fué actualizado"]);
    }

}
