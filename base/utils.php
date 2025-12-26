<?php 

class Utils {
    
    
    public static function url($relative_path="/"){
        /*
        genereer full links, iedere keer
        functie maken in utils-klasse -> vol path meegeven met alles, en dan link bouwen url($path tov 'applictie-/')
        bv: 
        app: https://mijnserver/<mijnapp>/

        url binnen applicatie: /users/{$id} -> utils::url("/users/{$id)"}
        gegenereerde url:
        https://mijnserver/<mijnapp>/users/{$id}
        */
        $config = new config();
        if(substr($config->SITE_URL,-1)=="/" && substr($relative_path,0,1) == "/"){
            $relative_path = substr($relative_path,1);
        }
        return $config->SITE_URL . $relative_path;
    }

    public static function redirect_to_relative_url($relative_path="/"){
        $url = Utils::url($relative_path);
        header('Location: '.$url);
        exit(0);
    }

    public static function redirect_to_absolute_url($url="/"){
        header('Location: '.$url);
        exit(0);
    }

    
}
