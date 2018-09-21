<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\FileObserver;
use App\Traits\HandlesPaths;

class File extends Model
{
    use HandlesPaths;
    
    protected $fillable = [
        'name',
        'description',
        'path',
        'file_name',
        'extension',
        'mime',
        'type',
        'public_path',
        'public',
        'file_size',
        'parent_id',
        'password',
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public static function boot()
    {
        File::observe(FileObserver::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(static::class, 'folder_id')->withoutGlobalScope('fsType');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(static::class, 'folder_id');
    }

        /**
     * Get url for previewing upload.
     *
     * @param string $value
     * @return string
     */
    public function getUrlAttribute($value)
    {
        if ($value) return $value;
        if ( ! isset($this->attributes['type']) || $this->attributes['type'] === 'folder') {
            return null;
        }

        if (Arr::get($this->attributes, 'public')) {
            return "storage/$this->public_path/$this->file_name";
        } else {
            return 'secure/uploads/'.$this->attributes['id'];
        }
    }

    public function getStoragePath()
    {
        return "$this->file_name/$this->file_name";
    }
     /**
     * Get path of specified entry.
     *
     * @param int $id
     * @return string
     */
    public function findPath($id)
    {
        $entry = $this->find($id, ['path']);
        return $entry ? $entry->getOriginal('path') : '';
    }

    /**
     * Return file entry name with extension.
     * @return string
     */
    public function getNameWithExtension() {
        if ( ! $this->exists) return '';

        $extension = pathinfo($this->name, PATHINFO_EXTENSION);

        if ( ! $extension) {
            return $this->name .'.'. $this->extension;
        }

        return $this->name;
    }

}