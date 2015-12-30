<?php

/*
 * This file is part of Gitlib.
 *
 * Copyright (C) 2015-2016 The Gitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitlib;

use Gitlib\Client as BaseClient;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumanApplication;

class GitlibServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig($this->app);
    }

    /**
     * Setup the config.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     */
    protected function setupConfig(Application $app)
    {
        $source = realpath(__DIR___.'/../config/gitlib.php');

        if ($app instanceof LaravelApplication && $app->runningInConsole()) {
            $this->publishes([$source => config_path('gitlib.php')]);
        } elseif ($app instanceof LumanApplication) {
            $app->configure('gitlib');
        }

        $this->mergeConfigFrom($source, 'gitlib');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerGitlib($this->app);
    }

    /**
     * Register the git client class.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     */
    protected function registerGitlib(Application $app)
    {
        $app->singleton('gitlib', function ($app) {
            return new BaseClient(null);
        });

        $app->alias('gitlib', BaseClient::class);
    }
}
