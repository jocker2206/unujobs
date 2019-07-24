<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Categoria;
use App\Models\Planilla;
use App\Models\TypeRemuneracion;
use Illuminate\Http\Request;

class CargoController extends Controller
{

    public function index()
    {
        $cargos = Cargo::all();
        return view("cargos.index", compact('cargos'));
    }


    public function create()
    {
        $planillas = Planilla::all();
        return view('cargos.create', \compact('planillas'));
    }


    public function store(Request $request)
    {
        $this->validate(request(), [
            "descripcion" => "required|unique:cargos",
            "planilla_id" => "required",
            "tag" => "required"
        ]);

        Cargo::create($request->all());

        return back()->with(["success" => "El registro se guardo correctamente!"]);
    }


    public function show(Cargo $cargo)
    {
        //
    }


    public function edit(Cargo $cargo)
    {
        $planillas = Planilla::all();
        return view('cargos.edit', \compact('planillas', 'cargo'));
    }


    public function update(Request $request, Cargo $cargo)
    {
        $this->validate(request(), [
            "descripcion" => "required|unique:cargos,id,".$request->descripcion,
            "planilla_id" => "required",
            "tag" => "required"
        ]);

        $cargo->update($request->all());

        return back()->with(["success" => "El registro se actualizó correctamente!"]);
    }


    public function destroy(Cargo $cargo)
    {
        //
    }


    public function categoria($id)
    {
        $cargo = Cargo::findOrFail($id);
        $notIn = $cargo->categorias->pluck(["id"]);
        $categorias = Categoria::whereNotIn('id', $notIn)->orderBy('nombre', 'ASC')->get();

        return view("cargos.categoria", compact('cargo', 'categorias'));
    }


    public function categoriaStore(Request $request, $id)
    {
        $this->validate(request(), [
            "categorias" => "required"
        ]);

        $categorias = $request->input("categorias", []);

        $cargo = Cargo::findOrFail($id);
        $cargo->categorias()->syncWithoutDetaching($categorias);
        return back()->with(["success" => "La categoria se agrego correctamente"]);
    }


    public function config($id)
    {
        $cargo = Cargo::findOrFail($id);
        $types = TypeRemuneracion::all();

        // return $cargo->types;

        foreach ($types as $type) {
           if ($cargo->typeRemuneracions->count() > 0) {
                $checked = $cargo->typeRemuneracions->where("id", $type->id)->count() ? true : false;
                $type->checked = $checked;
           } else {
               $type->checked = false;
           }
        }

        return view("cargos.config", compact('cargo', 'types'));
    }

    public function configStore(Request $request, $id)
    {
        $cargo = Cargo::findOrFail($id);
        $types = $request->input('types', []);
        $cargo->typeRemuneracions()->sync($types);
        return back();
    }

}
