<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;

use Auth;


class User extends Authenticatable implements MustVerifyEmailContract
{
    use MustVerifyEmailTrait;
	
    use Notifiable  {
	notify as protected laravelNotify;
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

	public function favoriteProducts()
    {
        return $this->belongsToMany(Product::class, 'user_favorite_products')
            ->withTimestamps()
            ->orderBy('user_favorite_products.created_at', 'desc');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function customers()
    {
	return $this->hasMany(Customer::class);
    }

    public function notify($instance)
    {
	if (method_exists($instance, 'toDatabase')) {
	  $this->increment('notification_count');
        }
	$this->laravelNotify($instance);
    }

    public function markAsRead()
    {
	$this->notification_count = 0;	
    	$this->save();
	$this->unreadNotifications->markAsRead();
    }
}
