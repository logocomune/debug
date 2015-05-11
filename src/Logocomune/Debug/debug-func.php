<?php
/**
 * Debug this var
 *
 * @param $var
 */
if (!function_exists('debug')) {
    function debug($var)
    {
        \Logocomune\Debug::debug($var, 2);
    }
}

/**
 * Debug
 * @param $var
 */
if (!function_exists('d')) {
    function d($var)
    {
        \Logocomune\Debug::debug($var, 2);
    }
}

/**
 * Debug and exit
 *
 * @param $var
 */
if (!function_exists('de')) {
    function de($var)
    {

        \Logocomune\Debug::debug($var, 2);
        exit;
    }
}
