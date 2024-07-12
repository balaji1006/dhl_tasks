<?php
namespace Drupal\dhl_location_finder\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;

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
            '#required' => false,
            '#weight' => '3',
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Find Locations'),
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
        dump($country_code);
        exit;
    }
}
