<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['user_id','status','total'];
    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id',);
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id',);
    }


    public function products()
    {
        return $this->belongsToMany(Product::class,'order_product', 'order_id', 'product_id')
                ->withPivot('quantity', 'subTotal');
    }
}
