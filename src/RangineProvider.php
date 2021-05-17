<?php

namespace W7\Validate\Ide\Helper;

use W7\Console\Application;
use W7\Core\Provider\ProviderAbstract;

class RangineProvider extends ProviderAbstract
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $application = $this->getContainer()->singleton(Application::class);
        $application->add(new IdeHelperCommand());
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    public function providers(): array
    {
        return [Application::class];
    }
}
