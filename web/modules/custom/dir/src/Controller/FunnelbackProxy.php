<?php

namespace Drupal\dir\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for DIR routes.
 */
class FunnelbackProxy extends ControllerBase {

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
