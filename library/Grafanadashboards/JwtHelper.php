<?php

namespace Icinga\Module\Grafanadashboards;

use Firebase\JWT\JWT;

class JwtHelper
{
    private $pkiDir;
    private $name;
    private $username;
    private $ttl;
    private $email;

    public function __construct($name,$username, $ttl, $email, $pkiDir)
    {
        $this->pkiDir = $pkiDir;
        $this->name = $this->makeFilesystemSafeString($name);
        $this->username = $username;
        $this->ttl = $ttl;
        $this->email = $email;

        if(! file_exists($this->pkiDir)){
            mkdir($this->pkiDir,0755,true);
        }
    }
    public function generateJwtToken()
    {
        $key = $this->getPrivateKey();
        if($key === false){
            return false;
        }
        $payload = [
            'user' => $this->username,
            'sub' => $this->email,
            'iat' => time(),
            'exp' => time()+$this->ttl,
        ];

        /**
         * IMPORTANT:
         * You must specify supported algorithms for your application. See
         * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
         * for a list of spec-compliant algorithms.
         */
        $jwt = JWT::encode($payload, $key, 'RS256');
        return $jwt;
    }
    private function makeFilesystemSafeString($string) {
        // Replace spaces with underscores
        $string = str_replace(' ', '_', $string);

        // Convert to lowercase
        $string = strtolower($string);

        // Remove any character that is not a letter, number, underscore, or hyphen
        $string = preg_replace('/[^a-z0-9-_]/', '', $string);

        // Optionally, truncate the string to a certain length (e.g., 255 characters for some filesystems)
        $string = substr($string, 0, 255);

        return $string;
    }

    public function generateKeypair($force=false){

        if(! file_exists($this->getPrivateKeyPath()) || ! file_exists($this->getPublicKeyPath()) || $force){

            $config = array(
                "private_key_bits" => 4096,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            );

            // Generate a new private (and public) key pair
            $res = openssl_pkey_new($config);

            // Extract the private key into a variable
            openssl_pkey_export($res, $privateKey);

            // Extract the public key into a variable
            $publicKeyDetails = openssl_pkey_get_details($res);
            $publicKey = $publicKeyDetails["key"];

            file_put_contents($this->getPrivateKeyPath(), $privateKey);
            chmod($this->getPrivateKeyPath(),0750);
            file_put_contents($this->getPublicKeyPath(), $publicKey);
            chmod($this->getPublicKeyPath(),0755);
        }



    }
    public function getPrivateKeyPath(){
        return $this->pkiDir.DIRECTORY_SEPARATOR.$this->name.'_private_key.pem';
    }
    public function getPublicKeyPath(){

        return $this->pkiDir.DIRECTORY_SEPARATOR.$this->name.'_public_key.pem';
    }

    public function getPublicKey(){
        if(file_exists($this->getPublicKeyPath())){
            return file_get_contents($this->getPublicKeyPath());
        }else{
            return false;
        }

    }
    private function getPrivateKey(){

        if(file_exists($this->getPrivateKeyPath())){
            return file_get_contents($this->getPrivateKeyPath());
        }else{
            return false;
        }
    }

}