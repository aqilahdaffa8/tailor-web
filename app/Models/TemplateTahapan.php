<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateTahapan extends Model
{
    use HasFactory;

    protected $table = 'template_tahapan';

    protected $fillable = [
        'jenis_jasa_id',
        'nama_tahapan',
        'urutan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<JenisJasa, $this>
     */
    public function jenisJasa(): BelongsTo
    {
        return $this->belongsTo(JenisJasa::class, 'jenis_jasa_id');
    }
}
