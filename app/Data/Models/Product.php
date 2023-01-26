<?php

namespace App\Data\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class Product
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Product whereId($value)
 * @method static Builder|Product whereName($value)
 * @method static Builder|Product whereDescription($value)
 * @method static Builder|Product wherePrice($value)
 * @method static Builder|Product whereIsActive($value)
 */
class Product extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'products';

    protected $fillable = [];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /** @var string[] */
    #[ArrayShape([
        'price' => 'float',
        'is_active' => 'bool',
    ])]
    protected $casts = [];

}
