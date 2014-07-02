<?php
require_once('AbstractContact.php');
class MicrosoftContact extends AbstractContact{  

    /*
     * Many thanks to:
     * http://www.walkswithme.net/hotmail-contact-list-reader-api-in-php-msn-oauth
    */

    // Load the config file.
    function __construct(){
        $this->auth_url = 'https://login.live.com/oauth20_authorize.srf?';
  
        $this->auth_params = array(
            'client_id'          => '',
            'scope'               => 'wl.signin%20wl.basic%20wl.emails%20wl.contacts_emails',
            'response_type' => 'code',
            'redirect_uri'    => ''
        );

        // Parameters sent to Google to get email addresses.
        $this->token_params = array(
            'client_id'          => '',
            'redirect_uri'    => '',
            'client_secret'    => '',
            'grant_type'    => 'authorization_code'
        );      
      
        $config_path =  realpath(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'microsoft_config.json';
        $this->fetchConfig($config_path);
    }

    // shouldn't override the method here..
    // but microsoft doesn't html escape the rerirect_uri.
    // override it as a quick solution.
    function getAuthUrl(){
        return 'https://login.live.com/oauth20_authorize.srf?' . 
            'client_id=' . $this->auth_params['client_id'] .
            '&scope=' . $this->auth_params['scope'] . 
            '&response_type=' . $this->auth_params['response_type'] .
            '&redirect_uri=' . $this->auth_params['redirect_uri'];            
    }
    
    function proceedForToken(){
      
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
    
    function proceedForContacts(){
        $url = 'https://apis.live.net/v5.0/me/contacts';
        $query = array(
            'access_token' => $this->getAccessToken()
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

}
