<?php
namespace Drupal\dhl_location_finder\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Yaml\Yaml;

class LocationFinderController extends ControllerBase
{
    public function locationResults($results)
    {
      return [
        '#type' => 'markup',
        '#markup' => '<pre>' . htmlspecialchars($results) . '</pre>',
      ];
    }    
}
