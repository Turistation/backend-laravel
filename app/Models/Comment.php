<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'comment',
        'name',
        'star',
        'blogs_id',
    ];

    public function blog()
    {   
        return $this->belongsTo(Blog::class, 'blogs_id', 'id');
    }
}
