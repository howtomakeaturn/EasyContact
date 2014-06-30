<?php

class MicrosoftContact{  


    /*
     * http://www.walkswithme.net/hotmail-contact-list-reader-api-in-php-msn-oauth
    */

    protected $code;

    protected $access_token;

    protected $contacts = array();
    
    // Parameters sent to Google to get access token.
    protected $auth_params = array(
        'client_id'          => '',
        'scope'               => 'wl.signin%20wl.basic%20wl.emails%20wl.contacts_emails',
        'response_type' => 'code',
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
        $config_path =  realpath(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'microsoft_config.json';
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
        return 'https://login.live.com/oauth20_authorize.srf?' . 
            'client_id=' . $this->auth_params['client_id'] .
            '&scope=' . $this->auth_params['scope'] . 
            '&response_type=' . $this->auth_params['response_type'] .
            '&redirect_uri=' . $this->auth_params['redirect_uri'];
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
      
        $url = 'https://login.live.com/oauth20_token.srf';
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
        $url = 'https://apis.live.net/v5.0/me/contacts';
        $query = array(
            'access_token' => $this->getAccessToken(),
            'limit' => '100'
        );
        
        $result = file_get_contents($url . '?' . http_build_query($query));
        $temp = json_decode($result,true);         

        foreach($temp['data'] as $emails){
            $email = implode(",",array_unique($emails['emails'])); //will get more email primary,sec etc with comma separate
            if (!$email){
                continue;
            }
            $email = rtrim($email,",");            
            $this->contacts[] = array( 
                'name' => $emails['name'], 
                'address' => $email
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
