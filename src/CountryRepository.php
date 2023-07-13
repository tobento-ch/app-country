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

namespace Tobento\App\Country;

use Tobento\Service\Country\CountryRepository as ServiceCountryRepository;
use Tobento\Service\Country\CountriesFactoryInterface;
use Tobento\Service\Country\CountriesInterface;
use Tobento\Service\Dir\DirsInterface;

/**
 * CountryRepository with supporting directories.
 */
class CountryRepository extends ServiceCountryRepository
{
    /**
     * Create a new CountryRepository.
     *
     * @param string $locale The default locale.
     * @param array<string, string> $localeFallbacks ['de-CH' => 'en-US']
     * @param array $localeMapping ['de' (requested) => '1' (stored)]
     * @param null|CountriesFactoryInterface $countriesFactory
     * @param null|string $directory
     * @param null|DirsInterface $dirs
     */
    public function __construct(
        protected string $locale = 'en',
        protected array $localeFallbacks = [],
        protected array $localeMapping = [],
        protected null|CountriesFactoryInterface $countriesFactory = null,
        protected null|string $directory = null,
        protected null|DirsInterface $dirs = null,
    ) {
        parent::__construct($locale, $localeFallbacks, $localeMapping, $countriesFactory, $directory);
    }

    /**
     * Fetches the countries from the file.
     *
     * @param null|string $locale
     * @return null|CountriesInterface
     */
    protected function fetchCountriesFromFile(null|string $locale): null|CountriesInterface
    {
        if (is_null($this->dirs)) {
            return parent::fetchCountriesFromFile($locale);
        }
        
        if (is_null($locale)) {
            return null;
        }
        
        // return the countries if they exist in the specific locale.
        if (isset($this->countries[$locale])) {
            return $this->countries[$locale];
        }
        
        if (!$this->isValidLocale($locale)) {
            return null;
        }
        
        foreach($this->dirs->all() as $directory) {
            
            $file = $directory->dir().$locale.'.json';

            if (!file_exists($file)) {
                continue;
            }

            return $this->countries[$locale] = $this->countriesFactory->createCountriesFromArray(
                countries: json_decode(file_get_contents($file), true),
                locale: $locale,
            );
        }
        
        return parent::fetchCountriesFromFile($locale);
    }
}