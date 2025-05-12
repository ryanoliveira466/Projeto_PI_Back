<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\User;


class Post extends Model
{
    use HasFactory;

    protected $table = 'components_user'; //table associed with model

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'javascript',
        'css',
        'html',
        'photo',
    ];


    public function user()
    {
        return $this->belongsTo(User::class); //Foreign key
    }



    protected static function booted()
    {
        static::creating(function ($user) {

            do {
                $length = rand(1, 10);
                $slug = '';
                for ($i = 0; $i < $length; $i++) {
                    $slug .= rand(0, 9);
                }
                $user->slug = $slug;
            } while (\App\Models\Post::where('slug', $slug)->exists());

            // do {
            //     $slug = Str::slug($user->name) . '-' . Str::random(6);
            //     $user->slug = $slug; 
            // } while (\App\Models\Post::where('slug', $slug)->exists());
        });

        // static::updating(function ($user) {
        //     if ($user->isDirty('name')) {
        //         do {
        //             $slug = Str::slug($user->name) . '-' . Str::random(6);
        //             $user->slug = $slug; 
        //         } while (\App\Models\Post::where('slug', $slug)->exists());
        //     }

        // });
    }
}
