<?php

namespace Itwmw\Validate\Ide\Helper\Provider;

use Illuminate\Support\ServiceProvider;
use Itwmw\Validate\Ide\Helper\IdeHelperCommand;

class LaravelProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                IdeHelperCommand::class
            ]);
        }
    }
}
