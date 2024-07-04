<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

<<<<<<<<<<<<<<  ✨ Codeium Command 🌟 >>>>>>>>>>>>>>>>
+    /**
+     * Disable automatic timestamping for this model.
+     *
+     * @var bool
+     */
+    public $timestamps = false;
-    public $timestamps=false;
 
     /**
      * Get the attributes that should be cast.
      *
      * @return array<string, string>
      */
     protected function casts(): array
     {
+        // Cast the 'email_verified_at' attribute to a datetime.
+        // Cast the 'password' attribute to a hashed string.
         return [
             'email_verified_at' => 'datetime',
             'password' => 'hashed',
         ];
     }
<<<<<<<  d9aa76ea-4061-41c0-8414-9f97bc50cf49  >>>>>>>
}
