<?php

namespace Drupal\helloworld\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class DefaultController.
 */
class DefaultController extends ControllerBase {

  /**
   * Hello.
   *
   * @return array
   *   Return Hello string.
   */
  public function hello() {
    $client = new \GuzzleHttp\Client([
        'base_uri' => 'https://api.quotable.io/',
       ]);
       
       $response = $client->get('random');
       $quote = json_decode($response->getBody(), TRUE);
       $cit = $quote['content'];
       $aut = $quote['author'];
       return [
       '#type' => 'markup',
       '#markup' =>
       $this->t("<h2>Nejaušs citāts</h2><h3>$cit</h3>$aut"),
    ];
  }

}