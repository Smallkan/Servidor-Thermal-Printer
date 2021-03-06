<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit03134ad56f432620df55893c6ea31b7d
{
    public static $files = array (
        'e88992873b7765f9b5710cab95ba5dd7' => __DIR__ . '/..' . '/hoa/consistency/Prelude.php',
        '3e76f7f02b41af8cea96018933f6b7e3' => __DIR__ . '/..' . '/hoa/protocol/Wrapper.php',
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
        'a4a119a56e50fbb293281d9a48007e0e' => __DIR__ . '/..' . '/symfony/polyfill-php80/bootstrap.php',
        'a1105708a18b76903365ca1c4aa61b02' => __DIR__ . '/..' . '/symfony/translation/Resources/functions.php',
        '4b7476a768c48566b152323c69f12e6d' => __DIR__ . '/..' . '/hoa/websocket/Socket.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Polyfill\\Php80\\' => 23,
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Contracts\\Translation\\' => 30,
            'Symfony\\Component\\Translation\\' => 30,
        ),
        'M' => 
        array (
            'Mike42\\' => 7,
        ),
        'H' => 
        array (
            'Hoa\\Websocket\\' => 14,
            'Hoa\\Stream\\' => 11,
            'Hoa\\Socket\\' => 11,
            'Hoa\\Protocol\\' => 13,
            'Hoa\\Http\\' => 9,
            'Hoa\\Exception\\' => 14,
            'Hoa\\Event\\' => 10,
            'Hoa\\Consistency\\' => 16,
        ),
        'C' => 
        array (
            'ClubeASC\\ThermalPrinter\\' => 24,
            'Carbon\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Polyfill\\Php80\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-php80',
        ),
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Contracts\\Translation\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/translation-contracts',
        ),
        'Symfony\\Component\\Translation\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/translation',
        ),
        'Mike42\\' => 
        array (
            0 => __DIR__ . '/..' . '/mike42/escpos-php/src/Mike42',
        ),
        'Hoa\\Websocket\\' => 
        array (
            0 => __DIR__ . '/..' . '/hoa/websocket',
        ),
        'Hoa\\Stream\\' => 
        array (
            0 => __DIR__ . '/..' . '/hoa/stream',
        ),
        'Hoa\\Socket\\' => 
        array (
            0 => __DIR__ . '/..' . '/hoa/socket',
        ),
        'Hoa\\Protocol\\' => 
        array (
            0 => __DIR__ . '/..' . '/hoa/protocol',
        ),
        'Hoa\\Http\\' => 
        array (
            0 => __DIR__ . '/..' . '/hoa/http',
        ),
        'Hoa\\Exception\\' => 
        array (
            0 => __DIR__ . '/..' . '/hoa/exception',
        ),
        'Hoa\\Event\\' => 
        array (
            0 => __DIR__ . '/..' . '/hoa/event',
        ),
        'Hoa\\Consistency\\' => 
        array (
            0 => __DIR__ . '/..' . '/hoa/consistency',
        ),
        'ClubeASC\\ThermalPrinter\\' => 
        array (
            0 => __DIR__ . '/../..' . '/entity',
        ),
        'Carbon\\' => 
        array (
            0 => __DIR__ . '/..' . '/nesbot/carbon/src/Carbon',
        ),
    );

    public static $classMap = array (
        'Attribute' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/Attribute.php',
        'Stringable' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/Stringable.php',
        'UnhandledMatchError' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/UnhandledMatchError.php',
        'ValueError' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/ValueError.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit03134ad56f432620df55893c6ea31b7d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit03134ad56f432620df55893c6ea31b7d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit03134ad56f432620df55893c6ea31b7d::$classMap;

        }, null, ClassLoader::class);
    }
}
