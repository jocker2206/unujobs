<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Concepto;
use Illuminate\Http\Request;
use App\Models\TypeRemuneracion;
use \DB;

class CategoriaController extends Controller
{

    public function index()
    {
        $categorias = Categoria::paginate(20);
        return view("categorias.index", \compact('categorias'));
    }


    public function create()
    {
        return view('categorias.create');
    }

 
    public function store(Request $request)
    {
        $this->validate(request(), [
            "nombre" => "required"
        ]);

        $categoria = Categoria::create($request->all());
        return back()->with(["success" => "El registro se guardo correctamente"]);
    }


    public function show(Categoria $categoria)
    {
        //
    }


    public function edit($id)
    {
        $categoria = Categoria::findOrFail($id);
        return view("categorias.edit", compact('categoria'));
    }


    public function update(Request $request, Categoria $categoria)
    {
        $this->validate(request(), [
            "nombre" => "required"
        ]); 

        return back()->with(["success" => "El registro se actualizó correctamente"]);
    }


    public function destroy(Categoria $categoria)
    {
        //
    }


    public function concepto($id)
    {
        $categoria = Categoria::findOrFail($id);
        $notIn = $categoria->conceptos->pluck(["id"]);
        $conceptos = Concepto::whereNotIn("id", $notIn)->get();
        return view("categorias.concepto", compact('categoria', 'conceptos'));
    }

    public function conceptoStore(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);
        $concepto = Concepto::findOrFail($request->concepto_id);
        $monto = $request->monto;

        $categoria->conceptos()->syncWithoutDetaching($concepto->id);
        $categoria->conceptos()->updateExistingPivot($concepto->id, ["monto" => $monto]);
        return back()->with(["success" => "El concepto se añadio correctamente"]);
    }


    public function config($id)
    {
        $categoria = Categoria::with('conceptos')->findOrFail($id);
        $types = TypeRemuneracion::all();
        $tmpType = DB::table('concepto_type_remuneracion')->where('categoria_id', $id)->get();

        $conceptos = $categoria->conceptos->filter(function ($con) {
            $con->check = false;
            return $con;
        });

        foreach ($types as $type) {
            $tmpConceptos = [];
            foreach ($conceptos as $concepto) {
                $tmp = $tmpType->where("type_remuneracion_id", $type->id)->where("concepto_id", $concepto->id);
                $concepto->check = $tmp->count();
                array_push($tmpConceptos, (object)[
                    "id" => $concepto->id,
                    "descripcion" => $concepto->descripcion,
                    "check" => $concepto->check
                ]);
            }
            
            $type->conceptos = $tmpConceptos;
        }

        return view('categorias.remuneracion', compact('categoria', 'types'));
    }


    public function configStore(Request $request, $id)
    {
        $this->validate(request(), [
            "type_remuneracion_id" => "required"
        ]);

        $categoria = Categoria::findOrFail($id);
        $conceptos = $request->input('conceptos', []);
        $payload = [];

        DB::table('concepto_type_remuneracion')->where("categoria_id", $categoria->id)
            ->where('type_remuneracion_id', $request->type_remuneracion_id)
            ->delete();

        foreach ($conceptos as $concepto) {
            $tmp = $categoria->conceptos->find($concepto);
            $monto = $tmp->pivot->monto;
            array_push($payload, [
                "concepto_id" => $tmp->id,
                "type_remuneracion_id" => $request->type_remuneracion_id,
                "categoria_id" => $categoria->id,
                "monto" => $monto
            ]);
        }

        DB::table('concepto_type_remuneracion')->insert($payload);

        return back();

    }

}
