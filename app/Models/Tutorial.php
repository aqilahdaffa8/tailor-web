<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Tutorial extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    public const TIPE = ['artikel', 'video', 'pdf'];

    protected $table = 'tutorial';

    protected $fillable = [
        'judul',
        'kategori',
        'kategori_tutorial_id',
        'tipe',
        'konten',
        'file_or_url',
    ];

    /**
     * @return BelongsTo<KategoriTutorial, $this>
     */
    public function kategoriTutorial(): BelongsTo
    {
        return $this->belongsTo(KategoriTutorial::class, 'kategori_tutorial_id');
    }

    /**
     * @var array<int, string>
     */
    protected $appends = ['embed_url', 'file_url'];

    /**
     * Ambil ID video dari tautan YouTube (watch, youtu.be, embed, shorts, live); null bila bukan YouTube.
     */
    public static function idYoutube(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $bagian = parse_url(trim($url));

        if (! $bagian || ! in_array($bagian['scheme'] ?? '', ['http', 'https'], true)) {
            return null;
        }

        $host = preg_replace('/^(www\.|m\.)/', '', strtolower($bagian['host'] ?? ''));
        $path = $bagian['path'] ?? '';
        $id = null;

        if ($host === 'youtu.be') {
            $id = explode('/', ltrim($path, '/'))[0];
        } elseif (in_array($host, ['youtube.com', 'youtube-nocookie.com'], true)) {
            if ($path === '/watch') {
                parse_str($bagian['query'] ?? '', $query);
                $id = is_string($query['v'] ?? null) ? $query['v'] : null;
            } elseif (preg_match('#^/(embed|shorts|live)/([^/?]+)#', $path, $cocok)) {
                $id = $cocok[2];
            }
        }

        return $id && preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : null;
    }

    /**
     * URL embed (iframe) untuk tutorial bertipe video.
     */
    public function getEmbedUrlAttribute(): ?string
    {
        if ($this->tipe !== 'video') {
            return null;
        }

        $id = self::idYoutube($this->file_or_url);

        return $id ? "https://www.youtube-nocookie.com/embed/{$id}" : null;
    }

    /**
     * URL publik file (PDF atau gambar artikel) di disk public; null bila tidak ada file.
     */
    public function getFileUrlAttribute(): ?string
    {
        if (! in_array($this->tipe, ['pdf', 'artikel'], true) || ! $this->file_or_url) {
            return null;
        }

        return Storage::disk('public')->url($this->file_or_url);
    }
}
