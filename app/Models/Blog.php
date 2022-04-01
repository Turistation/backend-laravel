<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title',
        'description',
        'blog_categories_id',
        'admins_id',
    ];

    public function admin_blog()
    {
        return $this->belongsTo(User::class, 'admins_id', 'id');
    }

    public function blog_category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_categories_id', 'id');
    }

    public function blog_gallery()
    {
        return $this->hasMany(BlogGallery::class, 'blogs_id', 'id');
    }

    public function blog_comments()
    {
        return $this->hasMany(Comment::class, 'blogs_id', 'id');
    }
}
