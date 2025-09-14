<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','offering_skill_id','looking_skill_id','start_date','end_date','available_from','available_to','preferred_days','gender_pref','location','session_type','use_username','status'
    ];

    protected $casts = [
        'preferred_days' => 'array',
        'use_username' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function offeringSkill() { return $this->belongsTo(Skill::class, 'offering_skill_id', 'skill_id'); }
    public function lookingSkill() { return $this->belongsTo(Skill::class, 'looking_skill_id', 'skill_id'); }
    public function requests() { return $this->hasMany(TradeRequest::class); }
    public function messages() { return $this->hasMany(TradeMessage::class); }
    public function tasks() { return $this->hasMany(TradeTask::class); }
}


