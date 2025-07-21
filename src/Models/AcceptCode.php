<?php

namespace AcceptCode\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcceptCode extends Model
{
    use HasFactory;

    protected $table = 'accept_codes';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'credetinal',
        'type',
        'slug',
        'created_at',
        'user_id',
        'updated_at'
    ];
}
