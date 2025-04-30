<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting_detail extends Model
{
    protected $fillable = [
        'name',
        'description',
        'dates',
        'user_id',
        'meeting_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}
