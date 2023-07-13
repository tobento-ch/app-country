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

namespace Tobento\App\Country\Test\Boot;

use PHPUnit\Framework\TestCase;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\App\Country\Boot\Country;
use Tobento\Service\Country\CountryFactoryInterface;
use Tobento\Service\Country\CountriesFactoryInterface;
use Tobento\Service\Country\CountryRepositoryInterface;
use Tobento\Service\Language\LanguageFactory;
use Tobento\Service\Language\Languages;
use Tobento\Service\Language\LanguagesInterface;
use Tobento\Service\Filesystem\Dir;

/**
 * CountryTest
 */
class CountryTest extends TestCase
{    
    protected function createApp(bool $deleteDir = true): AppInterface
    {
        if ($deleteDir) {
            (new Dir())->delete(__DIR__.'/../app/');
        }
        
        (new Dir())->create(__DIR__.'/../app/');
        (new Dir())->create(__DIR__.'/../app/config/');
        
        $app = (new AppFactory())->createApp();
        
        $app->dirs()
            ->dir(realpath(__DIR__.'/../../'), 'root')
            ->dir(realpath(__DIR__.'/../app/'), 'app')
            ->dir($app->dir('app').'config', 'config', group: 'config')
            ->dir($app->dir('root').'vendor', 'vendor');
        
        return $app;
    }
    
    public static function tearDownAfterClass(): void
    {
        (new Dir())->delete(__DIR__.'/../app/');
    }
    
    public function testInterfacesAreAvailable()
    {
        $app = $this->createApp();
        $app->boot(Country::class);
        $app->booting();
        
        $this->assertInstanceof(CountryFactoryInterface::class, $app->get(CountryFactoryInterface::class));
        $this->assertInstanceof(CountriesFactoryInterface::class, $app->get(CountriesFactoryInterface::class));
        $this->assertInstanceof(CountryRepositoryInterface::class, $app->get(CountryRepositoryInterface::class));
    }
    
    public function testRetrieveCountries()
    {
        $app = $this->createApp();
        $app->boot(Country::class);
        $app->booting();
        
        $countryRepository = $app->get(CountryRepositoryInterface::class);
        $countries = $countryRepository->findCountries();
        
        $this->assertSame('AF', $countries->first()?->code());
    }
    
    public function testAddCountries()
    {
        $app = $this->createApp();
        
        // for testing only one country is in files, usually you have all!
        $app->dirs()->dir(
            dir: $app->dir('root').'tests/countries-custom/',
            name: 'countries.custom',
            group: 'countries',
            priority: 300,
        );
        
        $app->boot(Country::class);
        $app->booting();
        
        $countryRepository = $app->get(CountryRepositoryInterface::class);
        $countries = $countryRepository->findCountries(locale: 'en-GB');
        
        $this->assertSame(1, count($countries->all()));
        $this->assertSame('Switzerland GB', $countries->first()?->name());
    }
    
    public function testCustomizeCountries()
    {
        $app = $this->createApp();
        
        // for testing only one country is in files, usually you have all!
        $app->dirs()->dir(
            dir: $app->dir('root').'tests/countries-custom/',
            name: 'countries.custom',
            group: 'countries',
            priority: 300,
        );
        
        $app->boot(Country::class);
        $app->booting();
        
        $countryRepository = $app->get(CountryRepositoryInterface::class);
        $countries = $countryRepository->findCountries(locale: 'en');
        
        $this->assertSame(1, count($countries->all()));
        $this->assertSame('Switzerland Custom', $countries->first()?->name());
    }
    
    public function testDefaultLocalesAreDefinedFromLanguagesIfAvailable()
    {
        $app = $this->createApp();
        
        $app->set(LanguagesInterface::class, function() {
            $factory = new LanguageFactory();

            $languages = new Languages(
                $factory->createLanguage('en', default: true),
                $factory->createLanguage('de', fallback: 'en'),
                $factory->createLanguage('it', fallback: 'en'),
                $factory->createLanguage('es', fallback: 'it'),
            );
            
            $languages->current('de');
            
            return $languages;
        });
        
        $app->boot(Country::class);
        $app->booting();
        
        $countryRepository = $app->get(CountryRepositoryInterface::class);
        
        $this->assertSame('Schweiz', $countryRepository->findCountry(code: 'CH')?->name());
        $this->assertSame('Svizzera', $countryRepository->findCountry(code: 'CH', locale: 'es')?->name());
    }
}