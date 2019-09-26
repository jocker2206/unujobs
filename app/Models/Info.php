<?php
/**
 * Models/Info.php
 * 
 * @author Hans Medina <twd2206@gmail.com>
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Modelo de la tabla infos
 * 
 * @category Models
 */
class Info extends Model
{
    
    /**
     * Los campos que solo serán alterados en la base de datos
     *
     * @var array
     */
    protected $fillable = [
        "categoria_id", "cargo_id", "work_id", "active", "observacion", 
        'meta_id', 'plaza', 'perfil', 'planilla_id', 'fuente_id', 'fuente', 
        'escuela', 'observacion', 'ruc', 'pap', 'sindicato_id'
    ];

    /**
     * Relacion de uno a mucho con la tabla works
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function work()
    {
        return $this->belongsTo(Work::class);
    }


    /**
     * Relacion de uno a mucho con la tabla cargos
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }


    /**
     * Relacion de uno a mucho con la tabla categorias
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }


    /**
     * Relacion de uno a mucho con la tabla metas
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function meta()
    {
        return $this->belongsTo(Meta::class);
    }


    /**
     * Relacion de uno a mucho con la tabla planillas
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function planilla()
    {
        return $this->belongsTo(Planilla::class);
    }

    /**
     * Relacion de muchos a mucho con la tabla cronogramas
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cronogramas()
    {
        return $this->belongsToMany(Cronograma::class, 'info_cronograma');
    }

    /**
     * Relacion de muchos a mucho con la tabla remuneracions
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function remuneraciones()
    {
        return $this->hasMany(Remuneracion::class);
    }


    /**
     * Relacion de muchos a mucho con la tabla descuentos
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function descuentos()
    {
        return $this->hasMany(Descuento::class);
    }

    /**
     * Relacion de uno a mucho con la tabla sindicatos
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sindicato()
    {
        return $this->belongsTo(Sindicato::class);
    }


    /* Relacion de uno a mucho con la tabla sindicatos
    *
    * @return Illuminate\Database\Eloquent\Relations\BelongsTo
    */
   public function obligaciones()
   {
       return $this->hasMany(Obligacion::class);
   }

    
    /**
     * Slug para proteger los id en las urls
     *
     * @return string
     */
    public function slug()
    {
        return \base64_encode($this->id);
    }
}
