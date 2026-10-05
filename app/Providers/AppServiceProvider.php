<?php

namespace App\Providers;

use App\Services\API\Agency\KPDNService;
use App\Services\API\Agency\JPNService;
use App\Services\API\SocialMedia\CheckpointCrawler;
use App\Services\API\SocialMedia\FacebookCrawler;
use App\Services\API\SocialMedia\InstagramCrawler;
use App\Services\API\SocialMedia\LinkedInCrawler;
use App\Services\API\SocialMedia\TikTokCrawler;
use App\Services\API\SocialMedia\TwitterCrawler;
use App\Services\SSOAuthService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->singleton(TwitterCrawler::class, TwitterCrawler::class);
        $this->app->singleton(LinkedInCrawler::class, LinkedInCrawler::class);
        $this->app->singleton(TikTokCrawler::class, TikTokCrawler::class);
        $this->app->singleton(FacebookCrawler::class, FacebookCrawler::class);
        $this->app->singleton(InstagramCrawler::class, InstagramCrawler::class);
        $this->app->singleton(CheckpointCrawler::class, CheckpointCrawler::class);
        $this->app->singleton(KPDNService::class, KPDNService::class);
        $this->app->singleton(JPNService::class, JPNService::class);
        $this->app->singleton(CentagateService::class, CentagateService::class);
        $this->app->singleton(CentagateErrorCode::class, CentagateErrorCode::class);
    }
}
