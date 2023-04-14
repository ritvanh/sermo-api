<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileView extends Model
{
    use HasFactory, HasRelationships;
    protected $fillable = [
        'visited_id',
        'visitor_id'
    ];
    protected $casts = [
        'visited_at' => 'datetime',
    ];
    public function visitor(){
        return $this->belongsTo(User::class);
    }
    public function visited(){
        return $this->belongsTo(User::class);
    }
}
