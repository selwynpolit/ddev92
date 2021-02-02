<?php

namespace Drupal\selwyn\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for selwyn routes.
 */
class SelwynController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
