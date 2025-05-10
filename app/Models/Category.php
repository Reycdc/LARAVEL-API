<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Models\Product;

class Category extends Model
{
    use HasFactory;

    // Daftar kolom yang boleh diisi (mass assignable)
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    // Mutator untuk membuat slug otomatis dari nama kategori
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function products()
{
    return $this->hasMany(Product::class);
}
}
