<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriTutorial extends Model
{
    use HasFactory;

    protected $table = 'kategori_tutorial';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
    ];

    /**
     * @return HasMany<Tutorial, $this>
     */
    public function tutorial(): HasMany
    {
        return $this->hasMany(Tutorial::class, 'kategori_tutorial_id');
    }
}
