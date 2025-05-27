<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class Transaksi extends Model
{
    

protected $table = 'transaksi';
protected $fillable = [
    'kode_transaksi',    
    'kode_referensi',
    'user_id',

];

//     public function category()
// {
//     // return $this->belongsTo(Category::class);
// }


public function user(){
    return $this->belongsTo(User::class);
}

}

