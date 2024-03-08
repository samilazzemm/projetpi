<?php 

namespace App\Service;

use DateTimeImmutable;

class JWTService 
{
    public function generate(array $header, array $payload , string $secret , int $validity = 3600) :string 
    {
        if($validity > 0){
            $now = new DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;
    
            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }

        // encode sur base 64 

        $base64Headeer = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        // clear values encoded 

        $base64Headeer = str_replace(['+','/','='], ['-','_',''] , $base64Headeer);
        $base64Payload = str_replace(['+','/','='], ['-','_',''] , $base64Payload);

        // generate signature

        $secret = base64_encode($secret);
        $signature = hash_hmac('sha256',$base64Headeer .'.' .$base64Payload , $secret , true);
        
        $base64Signature = base64_encode($signature);
        

        $base64Signature = str_replace(['+','/','='], ['-','_',''] , $base64Signature);
       



        //creation of token 

        $jwt = $base64Headeer . '.' . $base64Payload . '.' . $base64Signature; 
        return $jwt;
    }

    //token verfification 
    public function isValid(string $token): bool
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
            $token
        ) === 1;
    }

    // recupure payload 
    public function getPayload(string $token) : array
    {
        // n9asmou token
        $array = explode('.', $token);

        
        $payload = json_decode(base64_decode($array[1]), true);
        return $payload;
    }
    // recupure header
    public function getHeader(string $token) : array
    {
        // n9asmou token
        $array = explode('.', $token);


        $header = json_decode(base64_decode($array[0]), true);
        return $header;
    }

    // verification of expiration 

    public function isExpired(string $token):bool
    {
        $payload = $this->getPayload($token);
        $now = new DateTimeImmutable();

        return $payload['exp'] < $now->getTimestamp(); 
    }
    //verification of signature
    public function check(string $token, string $secret)
    {
        // nrecupuri l header wel payload
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        //regenerate token 

        $verifToken = $this->generate($header , $payload , $secret , 0);
        return $token === $verifToken;


    }
}