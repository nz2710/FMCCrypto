<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitc97ff65bd4101d2ceb2545a6f79c3498
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInitc97ff65bd4101d2ceb2545a6f79c3498', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitc97ff65bd4101d2ceb2545a6f79c3498', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitc97ff65bd4101d2ceb2545a6f79c3498::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
