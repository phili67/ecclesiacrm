<?php

namespace EcclesiaCRM;

class CacheProvider {
    /*
    * get entire cache for name $var
    $ $var (string) : name of the cache
    *
    * return the cache or null in the case of expiration
    */
    static public function get($var): ?array  {
        if (isset($_SESSION[$var])) {
            $res = $_SESSION[$var];

            if ($res['expire'] - time() < $res['duration']) {
                return $_SESSION[$var]['data'];
            }
            unset($_SESSION[$var]);
        }

        return null;
    }

    /*
    * get : duration
    $ $var (string) : name of the cache
    *
    * return the cache or null in the case of expiration
    */
    static public function duration($var): int  {
        if (isset($_SESSION[$var])) {
            $res = $_SESSION[$var];

            if ($res['expire'] - time() < $res['duration']) {
                return $res['duration'];
            }
            unset($_SESSION[$var]);
        }

        return -1;
    }

    /*
    * get : time Remaining
    $ $var (string) : name of the cache
    *
    * return the cache or null in the case of expiration
    */
    static public function timeRemaining($var): int  {
        if (isset($_SESSION[$var])) {
            $res = $_SESSION[$var];

            if ($res['expire'] - time() < $res['duration']) {
                return $res['expire'] - time();
            }
            unset($_SESSION[$var]);
        }

        return 0;
    }

    /*
    * add cache
    $ $var (string) : name of the cache
    * $data : anything you want
    * $duration : (int) in second
    *
    * return the cache for the variable
    */
    static public function add($var, $data, $duration): array {
        $_SESSION[$var] = [
            'data' => $data,
            'expire' => time() + $duration,
            'duration' => $duration
        ];
        
        return $_SESSION[$var];
    }

    static public function delete ($var): bool {
        if (isset($_SESSION[$var])) {            
            unset($_SESSION[$var]);
            return true;
        }
        return false;
    }

}