<?php

namespace App\Models;



use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Str;
use App\Models\Post;



class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
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
            // } while (\App\Models\User::where('slug', $slug)->exists());
        });

        // static::updating(function ($user) {
        //     if ($user->isDirty('name')) {
        //         do {
        //             $slug = Str::slug($user->name) . '-' . Str::random(6);
        //             $user->slug = $slug; 
        //         } while (\App\Models\User::where('slug', $slug)->exists());
        //     }

        // });
    }




    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
