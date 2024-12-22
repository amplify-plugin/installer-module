<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

if (! function_exists('is_active_route')) {
    /**
     * Set the active class to the current opened menu.
     *
     * @param  string|array  $route
     * @param  string  $className
     * @return string
     */
    function is_active_route($route, $className = 'active')
    {
        if (is_array($route)) {
            return in_array(Route::currentRouteName(), $route) ? $className : '';
        }
        if (Route::currentRouteName() == $route) {
            return $className;
        }
        if (strpos(URL::current(), $route)) {
            return $className;
        }
    }
}

if (! function_exists('is_installed')) {

    /**
     * Verify if Installation is completed and system
     * can work properly
     */
    function is_installed(): bool
    {
        return file_exists(storage_path('installed'));
    }
}
