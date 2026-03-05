<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeletedUser extends Model
{
    protected $table = 'deleted_users';

    protected $fillable = [
    'original_user_id', 
    'name', 
    'email', 
    'phone', 
    'full_profile_data', 
    'reason_for_deletion', 
    'deleted_at_time'
];

protected $casts = [
    'full_profile_data' => 'array', // Isse JSON automatic array ban jayega
    'deleted_at_time' => 'datetime'
];

    
}