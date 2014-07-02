<?php
require_once('AbstractContact.php');
class GoogleContact extends AbstractContact{  
    /*
     * Google OAuth2
     * https://developers.google.com/accounts/docs/OAuth2WebServer
     * 
     * Google Contact API 3
     * https://developers.google.com/google-apps/contacts/v3/?csw=1
     */

    // Load the config file.
    // Set params value.
    function __construct(){

        $this->auth_url = 'https://accounts.google.com/o/oauth2/auth?';

        $this->auth_params = array(
            'response_type' => 'code',
            'scope'               => 'https://www.googleapis.com/auth/contacts.readonly',
            'client_id'          => '',
            'redirect_uri'    => ''
        );

        $this->token_params = array(
            'client_id'          => '',
            'redirect_uri'    => '',
            'client_secret'    => '',
            'grant_type'    => 'authorization_code'
        );
      
        $config_path =  realpath(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'google_config.json';
        $this->fetchConfig($config_path);
    }
    
    
    // implement the process
    function proceedForToken(){
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

    // implement the process    
    function proceedForContacts(){
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

}
