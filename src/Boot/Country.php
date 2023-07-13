<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);
 
namespace Tobento\App\Country\Boot;

use Tobento\App\Boot;
use Tobento\App\Migration\Boot\Migration;
use Tobento\App\Country\CountryRepository;
use Tobento\Service\Country\CountryFactoryInterface;
use Tobento\Service\Country\CountryFactory;
use Tobento\Service\Country\CountriesFactoryInterface;
use Tobento\Service\Country\CountriesFactory;
use Tobento\Service\Country\CountryRepositoryInterface;
use Tobento\Service\Language\LanguagesInterface;

/**
 * Country
 */
class Country extends Boot
{
    public const INFO = [
        'boot' => [
            'installs country files',
            'implements country interfaces',
        ],
    ];

    public const BOOT = [
        Migration::class,
    ];
    
    /**
     * Boot application services.
     *
     * @param Migration $migration
     * @return void
     */
    public function boot(Migration $migration): void
    {
        // Add countries dir if not exists:
        if (! $this->app->dirs()->has('countries')) {
            $this->app->dirs()->dir(
                dir: $this->app->dir('app').'countries/',
                name: 'countries',
                group: 'countries',
                priority: 100,
            );
        }
        
        // Install country:
        $migration->install(\Tobento\App\Country\Migration\Country::class);
        
        $this->app->set(CountryFactoryInterface::class, CountryFactory::class);
        
        $this->app->set(CountriesFactoryInterface::class, function() {
            return new CountriesFactory(
                countryFactory: $this->app->get(CountryFactoryInterface::class),
            );
        });
        
        $this->app->set(CountryRepositoryInterface::class, function() {
            
            $locale = 'en';
            $localeFallbacks = [];
            
            if ($this->app->has(LanguagesInterface::class)) {
                $languages = $this->app->get(LanguagesInterface::class);
                $locale = $languages->current()->locale();
                $localeFallbacks = $languages->fallbacks('locale');
            }
            
            return new CountryRepository(
                locale: $locale,
                localeFallbacks: $localeFallbacks,
                countriesFactory: $this->app->get(CountriesFactoryInterface::class),
                dirs: $this->app->dirs()->sort()->group('countries'),
            );
        });
    }
}