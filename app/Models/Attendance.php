<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'time_in',
        'time_out',
        'notes',
        'user_id'
    ];

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function type(): Attribute
    {
        return new Attribute(
            get: fn ($value) =>  ["","masuk", "izin", "sakit"][$value],
        );
    }
}
