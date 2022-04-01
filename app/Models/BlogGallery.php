<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogGallery extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'photos_id',
        'blogs_id',
    ];

    public function photo()
    {
        return $this->hasMany(Photo::class, 'id', 'photos_id');
    }

    public function blog()
    {
        return $this->belongsTo(Blog::class, 'blogs_id', 'id');
    }
}
