<?php
/**
 * Http/Controllers/DescuentoController.php
 * 
 * @author Hans <twd2206@gmail.com>
 */
namespace App\Http\Controllers;

use App\Models\TypeDescuento;
use Illuminate\Http\Request;
use App\Models\Sindicato;
use App\Models\Afp;
use App\Models\ConfigDescuento;

/**
 * Class DescuentoController
 * 
 * @category Controllers
 */
class DescuentoController extends Controller
{

    /**
     * Muestra una lista de los tipos de descuentos
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $descuentos = TypeDescuento::with('config')->orderBy("key", "ASC")->get();
        return view('descuentos.index', compact(['descuentos']));
    }

    /**
     * Muestra un formulario para crear un nuevo tipo de descuento
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view("descuentos.create");
    }

    /**
     * Almacena un tipo de descuento recien creado
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            "descripcion" => "required",
            "key" => "required|unique:type_descuentos,key"
        ]);

        try {

            $type = TypeDescuento::create($request->all());

            return [
                "status" => true,
                "message" => "Los datos se guardarón correctamente"
            ];

        } catch (\Throwable $th) {
            
            return [
                "status" => false,
                "message" => "Ocurrió un error al procesar la operación"
            ];

        }
    }


    /**
     * Undocumented function
     *
     * @param Descuento $descuento
     * @return void
     */
    public function show(Descuento $descuento)
    {
        return back();
    }


    /**
     * Muestra un formulario para editar un tipo de descuento
     *
     * @param  string  $slug
     * @return \Illuminate\View\View
     */
    public function edit($slug)
    {
        $id = \base64_decode($slug);
        $type = TypeDescuento::findOrFail($id);
        return view("descuentos.edit", \compact('type'));
    }


    /**
     * Actualiza un tipo de descuento recien modificado
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate(request(), [
            "descripcion" => "required"
        ]);

        try {
            
            $type = TypeDescuento::findOrFail($id);
            
            $monto = $request->input("monto", 0);
            $obligatorio = $request->input("obligatorio", 0);
            $configs = $request->input("configs", []);
            $activo = $request->activo ? 1 : 0;

            ConfigDescuento::where("type_descuento_id", $type->id)->delete();

            foreach ($configs as $config) {
                $porcentaje = isset($config[0]) ? $config[0] : 0;
                $minimo = isset($config[1]) ? $config[1] : 0;
                $monto = isset($config[2]) ? $config[2] : 0;
                
                $current = ConfigDescuento::updateOrCreate([
                    "type_descuento_id" => $type->id
                ]);
                
                $current->update([
                    "porcentaje" => $porcentaje,
                    "minimo" => $minimo,
                    "monto" => $monto
                ]);

            }

            $type->update([
                "descripcion" => $request->descripcion,
                "obligatorio" => $obligatorio,
                "activo" => $activo
            ]);

            
            return [
                "status" => true,
                "message" => "Los datos se actualizarón correctamente"
            ];

        } catch (\Throwable $th) {

            \Log::info($th);

            return [
                "status" => false,
                "message" => "Ocurrió un error al procesar la operación"
            ];

        }
    }


    /**
     * Undocumented function
     *
     * @param Descuento $descuento
     * @return void
     */
    public function destroy(Descuento $descuento)
    {
        return back();
    }


    /**
     * Muestra un planel con checkbox para configurar los sindicatos y las afp's
     *
     * @param  string  $slug
     * @return \Illuminate\View\View
     */
    public function config($slug) 
    {
        $id = \base64_decode($slug);
        $type = TypeDescuento::findOrFail($id);
        $config = json_decode($type->config_afp);
        $sindicatos = Sindicato::all();

        $sindicatos->map(function($s) use($type) {
            $checked = $type->sindicatos->where("id", $s->id);
            $s->check = count($checked) > 0 ? true : false;
            return $s;
        });

        $tmps = [
            ["name" => "Flujo", "checked" => false],
            ["name" => "Mixta", "checked" => false],
            ["name" => "Aporte", "checked" => false],
            ["name" => "Prima", "checked" => false]
        ];

        $seguros = [];

        foreach ($tmps as $tmp) {

            $checked = false;
            if(is_array($config)) {
                foreach ($config as $con) {
                    if($con == strtolower($tmp['name'])) {
                        $checked = true;
                        break;
                    }
                }
            }

            $tmp['checked'] = $checked;
            array_push($seguros, $tmp);
        }

        return view('descuentos.config', compact('type', 'sindicatos', 'config', 'seguros'));
    }

    /**
     * Almacena las configuraciones de los sindicatos y afp's del tipo de descuento
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function configStore(Request $request, $id)
    {
        $type = TypeDescuento::findOrFail($id);

        $sindicatos = $request->input("sindicatos", []);
        $seguros = $request->input("seguros", []);

        $type->sindicatos()->sync($sindicatos);
        $type->update(["config_afp" => json_encode($seguros)]);

        return back()->with(["success" => "Los datos se guardarón correctamente"]);
    }

}
