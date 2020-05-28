<?php
namespace App\Utils;

use Firebase\JWT\JWT;

class JWTHelper{

  public static function generate($user)
  {
    $payload = [
      'iss' => config('app.name'), // Issuer of the token
      'sub' => $user['username'], // Subject of the token
      'iat' => time(), // Time when JWT was issued. 
      'username' => $user['username'],
      'name' => $user['name'],
      'email' => $user['email'],
      'exp' => time() + 60 * 60, // 1 hour
    ];

    return JWT::encode($payload, config('app.jwt_secret'));
  }

}