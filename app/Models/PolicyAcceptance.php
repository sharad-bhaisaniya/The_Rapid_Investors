<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyAcceptance extends Model
{
    protected $table = 'policy_acceptances';

    protected $fillable = [
        'title',
        'description',
        'content',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}