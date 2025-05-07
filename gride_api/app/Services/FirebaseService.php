<?php
namespace App\Services;

require base_path('vendor/autoload.php');
use Firebase\JWT\JWT;
use Google\Client;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Exception;

class FirebaseService
{
    protected $client;
    protected $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

    public function getAccessToken()
    {
        try {

            $serviceAccount = json_decode(file_get_contents(storage_path('app/public/service-account-file.json')), true);

            // JWT Claims
$issuedAt = time();
$expirationTime = $issuedAt + 3600;  // JWT expiration time (1 hour)

// JWT Header
$header = [
    'alg' => 'RS256',
    'typ' => 'JWT'
];

// JWT Payload
$payload = [
    'iss' => $serviceAccount['client_email'],  // Service account email
    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',  // The scope you're requesting access for
    'aud' => 'https://oauth2.googleapis.com/token',  // OAuth2 token endpoint
    'iat' => $issuedAt,  // Issued At time
    'exp' => $expirationTime  // Expiration time
];

// Encode JWT using the private key
$privateKey = $serviceAccount['private_key'];  // Private key from the service account file

$jwt = JWT::encode($payload, $privateKey, 'RS256');

// Request to get the access token
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

$postFields = [
    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
    'assertion' => $jwt  // The signed JWT
];

curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));

$response = curl_exec($ch);
curl_close($ch);

// Output the response
$tokenResponse = json_decode($response, true);

if(array_key_exists('access_token', $tokenResponse)){
    //echo $tokenResponse['access_token']; exit;
    return $tokenResponse['access_token'];
}
        

        } catch (Exception $e) {
            \Log::error('Error fetching access token: ' . $e->getMessage());
            echo 'Error fetching access token: ' . $e->getMessage();
            exit;
        }
     
        //return $credentials['access_token'];
    }
    
   
}



