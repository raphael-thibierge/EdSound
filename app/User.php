<?php

namespace App;

use Illuminate\Notifications\Notifiable;

class User extends \Jenssegers\Mongodb\Auth\User
{
    use Notifiable;

    /**
     * MongoDB collection for users
     *
     * @var string
     */
    protected $collection = 'users';

    /**
     * User collection primary key
     *
     * @var string
     */
    protected $primaryKey = '_id';

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
}
