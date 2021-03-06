<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    
    protected $fillable = [
        "type", "key", "name", "cronograma_id", "icono", 
        "type_report_id", "path", "read", "pendiente"
    ];

}
