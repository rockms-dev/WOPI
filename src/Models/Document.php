<?php

namespace MS\Wopi\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
