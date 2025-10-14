<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IdentidadeVisual extends Model
{
    use SoftDeletes;

    protected $table = 'identidade_visual';

    protected $fillable = [
        'entidade_type',
        'entidade_id',
        'logo_principal',
        'logo_horizontal',
        'logo_icone',
        'logo_marca_dagua',
        'cor_primaria',
        'cor_secundaria',
        'cor_acento',
        'cor_texto',
        'fonte_principal',
        'fonte_secundaria',
        'usar_logo_documentos',
        'usar_marca_dagua',
        'posicao_logo',
        'texto_rodape',
        'site',
        'telefone',
        'email',
        'observacoes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'usar_logo_documentos' => 'boolean',
        'usar_marca_dagua' => 'boolean',
    ];

    /**
     * Relacionamento polimórfico
     */
    public function entidade()
    {
        return $this->morphTo();
    }

    /**
     * Usuário que criou
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuário que atualizou
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get URL completa do logo principal
     */
    public function getLogoPrincipalUrlAttribute()
    {
        return $this->logo_principal 
            ? asset('storage/' . $this->logo_principal)
            : null;
    }

    /**
     * Get URL completa do logo horizontal
     */
    public function getLogoHorizontalUrlAttribute()
    {
        return $this->logo_horizontal 
            ? asset('storage/' . $this->logo_horizontal)
            : null;
    }

    /**
     * Get URL completa do logo ícone
     */
    public function getLogoIconeUrlAttribute()
    {
        return $this->logo_icone 
            ? asset('storage/' . $this->logo_icone)
            : null;
    }
}
