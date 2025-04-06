<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnnTest extends Model
{
    use HasFactory;

    protected $table = 'knn_test';
    protected $fillable = [
        'sex',
        'marital_status',
        'age',
        'education',
        'income',
        'occupation',
        'settlement_size',
        'predicted_settlement'
    ];
}
