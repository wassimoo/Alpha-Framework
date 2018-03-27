# Alpha-Framework

Alpha is a php framework based on MVC architecture with cross database support for query building and execution.

# Installation 

Simply download or clone this repository to your project folder, and require whatever component(s) you want to work with. (core folder only)

Example:
```php
  use AlphaRouter\Router;
  require_once __DIR__ . "/../core/router/Router.php";
  
```
This code will require Router Component.

Autoloader :<br>
    this project is currently under development, we are planing to introduce an autoloader in the near future.
  
  
# Another php Framework ? 
 
Absolutely not! Alpha is a reliable framework mainly made to be Simple, Hackable and can fit to your needs.

With many features under the hood, you can explore the power of components that it brings From <b> Session management </b> to <b> Cross database Query building and execution </b>.

We will introduce all these features in the next section.

# Features
* Session Management
 
    Start with requiring Alpha Session Manager:
    ```php
        use AlphaSession\Session;
        require_once __DIR__ . "/../core/session/session.php";
    ```
    
    Alpha Session gives you all session security aspects [introduced by php](http://php.net/manual/en/features.session.security.management.php) in one place.
    Instantiating a new Session will require up to 3 parameters which are essential for session security.
    * `TimeOut` specifies expiration time-stamp for session in seconds.
    * `regenIdInterval` specifies auto session id regeneration time-stamp in seconds.
    * `data` an array of data that must be present|set on every Session (example user_id) , otherwise Session is invalid.
    
    :exclamation: Note that these settings can be overwritten by calling `overwriteSettings()` at any time during Session object lifeTime.
    
    -> The session object must be present in the whole session lifetime, the best way to achieve this in php is by storing it in $_SESSION super global.<br>
    
    Example:
    ```php
        $_SESSION["info"] = new Session(100 , 5 , array("username" => "wassimoo"));
        // call  $_SESSION["info"]->validate() Every time you want to validate your session.
    ```
    
* Router

     Start with requiring Alpha Router :
        
     ```php
          use AlphaRouter\Router;
          require_once __DIR__ . "/../core/router/Router.php";
     ```
     Alpha Router is a powerful router that was made for the purpose of combining two factors 'rapidity' and 'efficiency'.
     
     * Auto-routing :
        
        Generally most of web developers prefer routing traffic to controller having the same name as the requested page.
        
        Example 1:
        
           "example.com/search/users" => "Controllers/search/users.php".
            Where search is a subdirectory containing user.php controller file.
            
        Example 2:
         
            "example.com/search" => "Controllers/search.php".
            
        Example 3:
         
            "example.com" => "Controllers/defaultController.php".     
                  
        Alpha Router offers you this functionality, by automatically matching controller based on requested url.
     
     * Regex Routing :
     
        This feature can be very useful if you want to give user more freedom when typing url.
        
        Example:
        
            `example.com/video` <=> `example.com/videos`.
        
         Where `example.com/videos?` can give you the same result.
         
     * Usage
     
        ```php
            $router = new Router($projectRoot, $defaultController, $autoResponseMatch)
        ```
        
        * `$projectRoot`   project absolute path where MVC exists
        * `$defaultController` (optional) defaultController absolute path, If not specified it will be determined automatically. This controller represents homepage controller.
        * `$autoResponseMatch` (optional) boolean , enable or disable Auto-routing.
        
        you can manually map request to target by calling `map()` method defined as follow:
        
        ```php
           map(String $request, String $response, bool $isRegex = false)
        ```
        
        Example: 
        ```php
           $router->map(".*search", "search.php", true);
        ```
        Where search.php is a valid php file containing `Search` class that extends Controller class.
        
        A controller is considered valid only if it has a public class similar to file name and extends Controller class.
       
        :exclamation: Routing Order
        1. Automatic routing (if is enabled).
         else
        2. direct routing (explicit request).
         else
        3. regex routing (regex request expression).
         else
        4. 404.html (if unable to match request using any of the above methods)
        
        If auto-router is unable to find the associated file it will continue search using the next methods.
       
       
       
