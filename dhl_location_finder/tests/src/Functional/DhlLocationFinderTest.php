<?php

namespace Drupal\Tests\dhl_location_finder\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the DHL Location Finder module.
 *
 * @group dhl_location_finder
 */
class DhlLocationFinderTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['dhl_location_finder'];

  /**
   * Tests that the form is displayed.
   */
  public function testFormDisplay() {
    $this->drupalGet('dhl-location-finder');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists('country');
    $this->assertSession()->fieldExists('addressLocality');
    $this->assertSession()->fieldExists('postal_code');
  }

  /**
   * Tests form submission.
   */
  public function testFormSubmission() {
    $this->drupalGet('dhl-location-finder');
    $this->submitForm([
      'country' => 'GB',
      'addressLocality' => 'London',
      'postal_code' => '01067',
    ], 'Find Locations');

    $this->assertSession()->pageTextContains('---');
  }

}
