<?php

namespace Drupal\srp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\srp\VotingPath;

/**
 * Returns responses for State Review Panel routes.
 */
class SrpController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $service = \Drupal::service('tempstore.shared');
    $collection = 'something';
    $uid = 1;
    $st_service = $service->get($collection, $uid);
    // Set some thing.
    $rc = $st_service->set('some_key', 'some value');
    // Get the value
    $rc = $st_service->get('some_key');


    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works dammit!'),
    ];

    return $build;
  }

  public function getPathItems() {
    $path_item = [];
    $path_items = [];

    $path_item = [
      'status' => 'accepted',
      'narrative' => [
        'citations' => [10,11,12],
        'status' => 'accepted'
      ],
      'activity' => [
        'citations' => [13,14,15],
        'status' => 'accepted'
      ],
    ];
    $path_items[100] = $path_item;
    $path_items[101] = $path_item;
    $path_items[102] = $path_item;

    $vp = new VotingPath($path_items);

    $vp[103] = $path_item;
    $path_item = $vp[103];
    $path_item['status'] = 'rejected';
    $vp[103] = $path_item;


    //something we would do???
    $vp[103].setNarrativeAccepted();
    $vp[103].setActivityAccepted();
    $vp[103].setAllAccepted();

    $vp['103']['narrative']['status'] = 'accepted';

    $build['content'] = [
      '#type' => 'item',
      '#markup' => "103:" . $vp[103]['status'],
    ];

    return $build;
  }

}
