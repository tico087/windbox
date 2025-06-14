<?php

if (!function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param mixed ...$args
     * @return void
     */
    function dd(...$args)
    {
       
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = $backtrace[0]['file'] ?? 'unknown file';
        $line = $backtrace[0]['line'] ?? 'unknown line';

       
        if (php_sapi_name() !== 'cli') {
            echo '<pre style="background-color: #1a202c; color: #fff; padding: 15px; border-radius: 8px; font-family: monospace; white-space: pre-wrap; word-wrap: break-word; font-size: 14px; line-height: 1.5;">';
            echo '<strong style="color: #66ff66;">' . htmlspecialchars($file) . ':' . htmlspecialchars($line) . '</strong><br>';
            echo '<hr style="border-top: 1px solid #4a5568; margin: 10px 0;">';
            foreach ($args as $x) {
                var_dump($x);
            }
            echo '</pre>';
        } else {
           
            echo "\n";
            echo "\033[1;32m" . $file . ":" . $line . "\033[0m\n"; 
            echo "----------------------------------------\n";
            foreach ($args as $x) {
                var_dump($x);
            }
            echo "----------------------------------------\n";
        }
        die(1); 
    }
}

if (!function_exists('d')) {
    /**
     * Dump the passed variables without ending the script.
     *
     * @param mixed ...$args
     * @return void
     */
    function d(...$args)
    {
        
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = $backtrace[0]['file'] ?? 'unknown file';
        $line = $backtrace[0]['line'] ?? 'unknown line';

       
        if (php_sapi_name() !== 'cli') {
            echo '<pre style="background-color: #2d3748; color: #cbd5e0; padding: 15px; border-radius: 8px; font-family: monospace; white-space: pre-wrap; word-wrap: break-word; font-size: 14px; line-height: 1.5;">';
            echo '<strong style="color: #63b3ed;">' . htmlspecialchars($file) . ':' . htmlspecialchars($line) . '</strong><br>';
            echo '<hr style="border-top: 1px solid #4a5568; margin: 10px 0;">';
            foreach ($args as $x) {
                var_dump($x);
            }
            echo '</pre>';
        } else {
          
            echo "\n";
            echo "\033[1;34m" . $file . ":" . $line . "\033[0m\n"; 
            echo "----------------------------------------\n";
            foreach ($args as $x) {
                var_dump($x);
            }
            echo "----------------------------------------\n";
        }
    }
}