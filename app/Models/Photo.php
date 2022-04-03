<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'photos',
    ];

    public function blogs()
    {
        // return $this->hasMany(BlogGallery::class, 'blogs_id', 'id');
        return $this->belongsToMany(Blog::class, 'blog_photo', 'photo_id', 'blog_id');
    }
}
