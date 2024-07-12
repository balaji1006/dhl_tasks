<?php
namespace Drupal\dhl_location_finder\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use GuzzleHttp\Client;
use Symfony\Component\Yaml\Yaml;

class LocationFinder extends FormBase
{
    public function getFormId()
    {
        return 'dhl_location_finder';
    }

    /**
     * Form constructor.
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $opt_lan = [];
        $site_languages = \Drupal::languageManager()->getNativeLanguages();
        foreach ($site_languages as $language_code => $language) {
            $opt_lan[$language_code] = $language->getName();
        }
        $current_language = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
        $form['#attached']['library'][] = 'dhl_location_finder/app-styling';
        $form['#attributes']['autocomplete'] = 'off';

        $form['country_code'] = [
            '#type' => 'textfield',
            '#title' => t('Country Code'),
            '#required' => true,
            '#maxlength' => 3,
            '#weight' => '1',
        ];

        $form['address_locality'] = [
            '#title' => t('Address Locality'),
            '#type' => 'textfield',
            '#required' => true,
            '#weight' => '2',
        ];

        $form['postal_code'] = [
            '#title' => t('Postal Code'),
            '#type' => 'textfield',
            '#required' => true,
            '#weight' => '3',
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => t('Find Locations'),
            '#weight' => '4',
        ];
        return $form;
    }

    /**
     * Validate the title and the checkbox of the form.
     *
     * @param array $form
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        parent::validateForm($form, $form_state);
        $fld_country_code = $form_state->getValue('country_code');
        // Check the country code valid or not valid
        if(!preg_match('/^[a-z]{2,3}$/', strtolower($fld_country_code))) {
            $form_state->setErrorByName('country_code', $this->t('Please provide valid Country Code.'));
        }
        
    }

    /**
     * Form submission handler.
     *
     * @param array $form
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $country_code = $form_state->getValue('country_code');
        $address_locality = $form_state->getValue('address_locality');
        $postal_code = $form_state->getValue('postal_code');

        $api_endpoint = 'https://api.dhl.com/location-finder/v1/find-by-address';
        $client = new Client();
        $response = $client->get($api_endpoint, [
          'headers' => [
            'DHL-API-Key' => 'demo-key',
          ],
          'query' => [
            'countryCode' => $country_code,
            'postalCode' => $postal_code,
            'addressLocality' => $address_locality,
          ],
        ]);
        $response = json_decode($response->getBody(), TRUE);


        $filtered_locations = [];
        foreach ($response['locations'] as $location) {
            if (self::isEvenAddress($location['place']['address']['streetAddress'])) {
                $opening_hours = [];
                foreach ($location['openingHours'] as $hours) {
                    if ($hours['dayOfWeek'] !== 'http://schema.org/Sunday' && $hours['dayOfWeek'] !== 'http://schema.org/Saturday') {
                        $opening_hours[] = $hours;
                    }
                }
                $location['openingHours'] = $opening_hours;
                $filtered_locations[] = $location;
            }
        }
        $results = '';
        foreach ($filtered_locations as $location) {
            $results .= Yaml::dump([
              'locationName' => $location['name'],
              'address' => [
                'countryCode' => $location['place']['address']['countryCode'],
                'postalCode' => $location['place']['address']['postalCode'],
                'addressLocality' => $location['place']['address']['addressLocality'],
                'streetAddress' => $location['place']['address']['streetAddress'],
              ],
              'openingHours' => $location['openingHours'],
            ], 2, 2);
        }        
        $this->messenger()->addStatus($results);
       }
    
    /**
     * Check if the street address number is even.
     */
    public static function isEvenAddress($street_address) {
        if (preg_match('/\d+/', $street_address, $matches)) {
            return $matches[0] % 2 !== 0;
        }
        return false;
    }


}
