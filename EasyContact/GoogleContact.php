<?php

class GoogleContact{  
    /*
     * Google OAuth2
     * https://developers.google.com/accounts/docs/OAuth2WebServer
     * 
     * Google Contact API 3
     * https://developers.google.com/google-apps/contacts/v3/?csw=1
     */
    
    // oauth2 authentication code
    protected $code;

    // the access token to call all the Google API
    protected $access_token;

    protected $contacts = array();
    
    // Parameters sent to Google to get access token.
    protected $auth_params = array(
        'response_type' => 'code',
        'scope'               => 'https://www.googleapis.com/auth/contacts.readonly',
        'client_id'          => '',
        'redirect_uri'    => ''
    );

    // Parameters sent to Google to get email addresses.
    protected $token_params = array(
        'client_id'          => '',
        'redirect_uri'    => '',
        'client_secret'    => '',
        'grant_type'    => 'authorization_code'
    );

    // Load the config file.
    function __construct(){
        $config_path =  realpath(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'google_config.json';
        $string = file_get_contents( $config_path );
        $config = json_decode($string,true);
        $this->setClientId($config['client_id']);
        $this->setRedirectUri($config['redirect_uri']);
        $this->setClientSecret($config['client_secret']);
    }
    
    function setClientId($client_id){
        $this->auth_params['client_id'] = $client_id;
        $this->token_params['client_id'] = $client_id;
    }

    function setRedirectUri($redirect_uri){
        $this->auth_params['redirect_uri'] = $redirect_uri;      
        $this->token_params['redirect_uri'] = $redirect_uri;      
    }
    
    function setClientSecret($client_secret){
        $this->token_params['client_secret'] = $client_secret;            
    }
    
    function getAuthUrl(){
        return 'https://accounts.google.com/o/oauth2/auth?' . 
            http_build_query($this->auth_params);
    }
    
    private function fetchCode(){
        $this->code = $_REQUEST['code'];
        $this->token_params['code'] = $_REQUEST['code'];
    }
    
    function getCode(){
        return $this->code;
    }
    /*
     * Send HTTP request to Google to get access token.
     */
    private function proceedForToken(){
        $url = 'https://accounts.google.com/o/oauth2/token';
        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($this->token_params),
            )
        );
        
        $context  = stream_context_create($options);
                
        $result = file_get_contents($url, false, $context);
        $response = json_decode($result, TRUE);
                
        $this->access_token = $response['access_token'];
        
        return TRUE;
    }
    
    function getAccessToken(){
        return $this->access_token;
    }
    
    /*
     * Send HTTP request to Google to get contacts.
     */
    private function proceedForContacts(){
        $url = 'https://www.google.com/m8/feeds/contacts/default/full';
        $query = array(
            'v' => '3.0',
            'access_token' => $this->getAccessToken(),
            'alt' => 'json'
        );
        
        $result = file_get_contents($url . '?' . http_build_query($query));
        $temp = json_decode($result,true);         

        foreach($temp['feed']['entry'] as $cnt) {
            // You think all entry has email address? Wrong!
            // Goodle doesn't check this for us. So sad :(((
            if ( !array_key_exists('gd$email', $cnt) ){
                continue;
            }
          
            $this->contacts[] = array( 
                'name' => $cnt['title']['$t'], 
                'address' => $cnt['gd$email']['0']['address'] 
            );
        }        
    }

    function getContacts(){
        $this->fetchCode();
        $this->proceedForToken();
        $this->proceedForContacts();      
        return $this->contacts;
    }

}
