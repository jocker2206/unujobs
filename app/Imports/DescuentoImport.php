<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;

use App\Models\Work;
use App\Models\Descuento;
use App\Models\TypeDescuento;
use App\Models\User;
use App\Notifications\BasicNotification;

class DescuentoImport implements ToCollection,WithChunkReading, WithHeadingRow, ShouldQueue
{
    
    use Importable;

    private $cronograma;

    public function __construct($cronograma, $name)
    {
        $this->cronograma = $cronograma;
        $this->name = $name;
    }

    public function collection(Collection $collection)
    {
        try {
            foreach ($collection as $iter => $row) {

                // obtenemos a todos los trabajadores que pertenecen al cronograma
                $workIn = Descuento::where("cronograma_id", $this->cronograma->id)
                    ->get()->pluck(["work_id"]);
                $works = Work::whereIn("id", $workIn)->get();
                // buscar al trabajador por numero de documento
                $work = $works->where("numero_de_documento", $row['numero_de_documento'])->first();
                // verificar si el trabajador existe
                if ($work) {
                    // obtenemos la informacion detallada del trabajador
                    foreach ($work->infos as $info) {
                        // obtenemos el tipo de remuneracion
                        $type = TypeDescuento::where("key", $row['descuento'])->first();
                        // verificamos que el typeRemuneracion exista
                        if ($type) {
                            // obtenemos las remuneraciones del trabajador
                            $descuento = Descuento::updateOrCreate([
                                "work_id" => $work->id,
                                "categoria_id" => $info->categoria_id,
                                "cargo_id" => $info->cargo_id,
                                "planilla_id" => $info->planilla_id,
                                "cronograma_id" => $this->cronograma->id,
                                "type_descuento_id" => $type->id,
                                "adicional" => $this->cronograma->adicional,
                            ]);
    
                            $descuento->update([
                                "monto" => isset($row['monto']) ? $row['monto'] : 0
                            ]);
                        }
                    }
                }
            }   
        } catch (\Throwable $th) {
            self::notify();
            return exit;
        }
    }


    public function chunkSize(): int
    {
        return 500;
    }


    public function notify()
    {
        $users = User::all();
    
        foreach ($users as $user) {
            $user->notify(new BasicNotification(
                "#", 
                "Ocurio un error al importar los descuentos ({$this->name})",
                "fas fa-times",
                "bg-danger"
            ));
        }
    }

}
