<?php

namespace Satmaxt\LaravelDist\Commands;

use Illuminate\Console\Command;
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
        if (is_dir($this->basePath('laravel'))) {
            $this->info('Deleting laravel directory...');
            $this->deleteFiles($this->basePath('laravel/'));
        }

        if (is_dir($this->basePath('dist'))) {
            $this->info('Deleting dist directory...');
            $this->deleteFiles($this->basePath('dist/'));
        }

        $this->info('Creating laravel and dist directory...');
        mkdir($this->basePath('laravel'));
        mkdir($this->basePath('dist'));

        $this->info('Copying core files to laravel...');
        foreach (config('laravel-dist.core') as $file) {

            if (is_dir($this->basePath($file))) {
                $this->copyDirectory($this->basePath($file), $this->basePath('laravel/' . $file));
            } elseif (is_file($this->basePath($file))) {
                copy($this->basePath($file), $this->basePath('laravel/' . $file));

                if ($file === '.env.example') {
                    copy($this->basePath($file), $this->basePath('laravel/.env'));
                    $env = file_get_contents($this->basePath('laravel/.env'));
                    $env = str_replace('APP_ENV=local', 'APP_ENV=production', $env);
                    file_put_contents($this->basePath('laravel/.env'), $env);
                }
            }

            $this->info('Copied ' . $file . ' to ' . $this->basePath('laravel/' . $file));
        }

        $this->info('Installing production dependencies...');
        chdir($this->basePath('laravel/'));
        system('composer install --no-dev --optimize-autoloader', $ret);
        $this->comment($ret);
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

    public function basePath($path = '')
    {
        return App::basePath($path);
    }
}
