<?php
/**
 * Created by PhpStorm.
 * User: raphael
 * Date: 24/11/2017
 * Time: 18:33
 */

namespace App;


use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string service
 * @property string access_token
 * @property string refresh_token
 * @property int token_expiration
 * @property array user_data
 */
class OAuthConnect extends Model
{

    const SPOTIFY = 'spotify';

    protected $fillable = [
        'service',
        'access_token',
        'refresh_token',
        'token_expiration',
        'user_data',
    ];

}