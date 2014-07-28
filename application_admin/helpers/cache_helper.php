<?php
/**
 * A shortcut function for cache. If the index is empty, usess the callback function for getting the value and saving to cache. Otherwise returns the cached value
 * @param string $index
 * @param function $callback
 * @param array $params Optional parameters for the callback function
 * @param object $object optional object, in which case the callback is a class method
 * @param boolean $enable If false, bypasses the caching. This makes it possible to switch caching on and off for individual queries
 * @param int $cache_time How long to cache the item, in seconds
 */
function get_or_save_cached($index, $callback, $params=array(), $object=null, $enable=true, $cache_time=60) {
    $ci = get_instance();
    $ci->load->driver('cache');

    if ($value = $ci->cache->apc->get($index) && $enable) {
        return $value;
    }

    if (empty($object)) {
        $value = call_user_func_array($callback, $params);
    } else {
        $value = call_user_func_array(array($object, $callback), $params);
    }

    if ($enable) {
        $ci->cache->apc->save($index, $value, $cache_time);
    }

    return $value;
}
