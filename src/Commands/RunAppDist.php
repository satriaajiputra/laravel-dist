<?php

namespace Satmaxt\LaravelDist\Commands;

use ZipArchive;
use RecursiveIteratorIterator;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use Illuminate\Support\Facades\App;

class RunLaravelDist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-dist:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start generating production';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // clean directory first
        if (is_dir($this->basePath('laravel'))) {
            $this->info('Deleting laravel directory...');
            $this->deleteFiles($this->basePath('laravel/'));
        }

        if (is_dir($this->basePath('dist'))) {
            $this->info('Deleting dist directory...');
            $this->deleteFiles($this->basePath('dist/'));
        }

        // remake directory
        $this->info('Creating laravel and dist directory...');
        mkdir($this->basePath('laravel'));
        mkdir($this->basePath('dist'));

        // copy core files
        $this->info('Copying core files to laravel...');
        foreach (config('laravel-dist.core') as $file) {

            if (is_dir($this->basePath($file))) {
                $this->copyDirectory($this->basePath($file), $this->basePath('laravel/' . $file));
            } elseif (is_file($this->basePath($file))) {
                copy($this->basePath($file), $this->basePath('laravel/' . $file));
            }

            $this->info('Copied ' . $file . ' to ' . $this->basePath('laravel/' . $file));
        }

        // install dependencies and optimization
        $this->info('Installing production dependencies...');
        chdir($this->basePath('laravel/'));
        system('composer install --no-dev --optimize-autoloader', $ret);
        system('php artisan optimize:clear');
        $this->comment('Dependencies installed successfully');

        // change .env to production
        $env = file_get_contents($this->basePath('laravel/.env'));
        $env = str_replace('APP_ENV=local', 'APP_ENV=production', $env);
        $env = str_replace('APP_DEBUG=true', 'APP_DEBUG=false', $env);
        file_put_contents($this->basePath('laravel/.env'), $env);

        // zip required folder
        $this->zipFolder($this->basePath('laravel'), $this->basePath('dist/laravel.zip'), 'laravel');
        $this->zipFolder($this->basePath('public/'), $this->basePath('dist/public.zip'));

        // delete unused folder
        $this->deleteFiles($this->basePath('laravel/'));
    }

    /**
     * Zip directory recursively
     *
     * @author Satria Aji Putra <satriamaxt@gmail.com>
     * @param string $target
     * @param string $destination
     * @param string $parentDir
     * @source https://stackoverflow.com/a/4914807/6147414
     * @since v1.0.0
     * @return void
     */
    public function zipFolder($target, $destination, $parentDir = false)
    {
        // Get real path for our folder
        $rootPath = realpath($target);

        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($parentDir) {
            $zip->addEmptyDir($parentDir);
        }

        // Create recursive directory iterator
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();

                if ($parentDir) {
                    $relativePath = $parentDir . DIRECTORY_SEPARATOR;
                } else {
                    $relativePath = '';
                }

                $relativePath .= substr($filePath, strlen($rootPath) + 1);

                // use index.php from template
                if (strpos($filePath, 'public/index.php') !== false) {
                    $zip->addFile(__DIR__ . '/../templates/index.php.tpl', 'index.php');
                    continue;
                }

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();
    }

    /**
     * Delete directory and files on it recursively
     *
     * @author Satria Aji Putra <satriamaxt@gmail.com>
     * @param string $target
     * @since v1.0.0
     * @return void
     */
    public function deleteFiles($target)
    {
        if (is_dir($target)) {
            $files = glob($target . '/{,.}*[!.]', GLOB_MARK | GLOB_BRACE);

            foreach ($files as $file) {
                $this->deleteFiles($file);
            }

            rmdir($target);
        } elseif (is_file($target)) {
            unlink($target);
        }
    }

    /**
     * Copy directory recursively
     *
     * @param string $source
     * @param string $destination
     * @since v1.0.0
     * @source https://phpcodesnippets.com/file-manipulation/bulk-copy-directory-recursively/
     * @return void
     */
    public function copyDirectory($source, $destination)
    {
        $dir = opendir($source);
        mkdir($destination);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($source . '/' . $file)) {
                    $this->copyDirectory($source . '/' . $file, $destination . '/' . $file);
                } else {
                    copy($source . '/' . $file, $destination . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Get base path of application
     *
     * @param string $path
     * @return string
     */
    public function basePath($path = '')
    {
        return App::basePath($path);
    }
}
