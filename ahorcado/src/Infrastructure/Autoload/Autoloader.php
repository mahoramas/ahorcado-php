<?php
declare(strict_types=1);

namespace App\Infrastructure\Autoload;

final class Autoloader
{
    private array $prefixes = [];

    /**
     * Registra el autoloader para un namespace base.
     *
     * @param string $prefix  Prefijo del namespace (por ejemplo, "App\\")
     * @param string $baseDir Directorio base donde se encuentran las clases
     */
    public static function register(string $prefix = 'App\\', string $baseDir = __DIR__ . '/../../'): void
    {
        $loader = new self();
        $loader->addNamespace($prefix, $baseDir);
        spl_autoload_register([$loader, 'loadClass']);
    }

    /**
     * Asocia un prefijo de namespace con un directorio base.
     */
    public function addNamespace(string $prefix, string $baseDir): void
    {
        $prefix = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->prefixes[$prefix] = $baseDir;
    }

    /**
     * Carga automáticamente una clase PHP basada en su namespace.
     */
    public function loadClass(string $class): void
    {
        foreach ($this->prefixes as $prefix => $baseDir) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                continue;
            }

            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

            if (is_file($file)) {
                require $file;
                return;
            }
        }
    }
}
