<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf35f84c7e75079b99a36850f36c76d5a
{
    public static $prefixesPsr0 = array (
        'F' => 
        array (
            'Flow\\JSONPath\\Test' => 
            array (
                0 => __DIR__ . '/..' . '/flow/jsonpath/tests',
            ),
            'Flow\\JSONPath' => 
            array (
                0 => __DIR__ . '/..' . '/flow/jsonpath/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInitf35f84c7e75079b99a36850f36c76d5a::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
