<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class CollectedUrl extends Model
{

    protected $table = 'urls';

    protected $fillable = [
        'url',
        'exist',
        'checked_at'
    ];
}
