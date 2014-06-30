# EasyContact
The fastest way to get users' email contact from Google Gmail, Yahoo Mail, and Microsoft Outlook.

#### 1. Create an application, get client id and secret at:

### Microsoft Outlook
https://account.live.com/developers/applications/create
### Google Gmail
https://console.developers.google.com/

#### 2. Get id, secret and set redirect uri in above consoles, set them in google_config.json, microsoft_config.json, and yahoo_config.json.

```javascript
{
    "client_id": "xxxxx",
    "redirect_uri": "http://xxxxxx",
    "client_secret": "xxxxx"
}
```

#### 3. Usage

Let's take Gmail for example:
```php
    // This is the controller method which redirect user to Goole authentication page.
    function auth_google(){
        $ec = new EasyContact();
        redirect($ec->google->getAuthUrl());
    }

    // This is the controller method Goole will redirect user back.    
    function receive_google(){
        $ec = new EasyContact();
        $contacts = $ec>google->getContacts();
        foreach($contacts as $contact){
            echo $contact['name'] . "：";
            echo $contact['address'];
            echo '<hr />';
        }
    }
```

That's all!
