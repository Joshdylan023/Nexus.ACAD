<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrandeAreaConhecimento extends Model
{
    use HasFactory;

    protected $table = 'grandes_areas_conhecimento';

    protected $fillable = ['nome'];
}
