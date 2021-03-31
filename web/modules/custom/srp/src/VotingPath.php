<?php


namespace Drupal\srp;


use Exception;
use Traversable;

class VotingPath implements \ArrayAccess, \IteratorAggregate {

  /**
   * @var array $pathItems
   */
  private array $pathItems;

  private array $sortedPathItems;

  /**
   * VotingPath constructor.
   *
   * @param $pathItems
   */
  public function __construct($path_items) {
    $this->pathItems = $path_items;
    //TODO: Do we want to do a sanity check here to check for
    // expectation NIDs etc?

    // Sort the whole shebang into expectation.sort_order.

    //Obtain a list of columns for each sort element
    $expectation  = array_column($this->pathItems, 'expectation_nid');
    $sort_order = array_column($this->pathItems, 'sort_order');

    // Sort the data with expectation ascending, sort_order ascending
    // Add $data as the last parameter, to sort by the common key
    array_multisort($expectation, SORT_ASC, $sort_order, SORT_ASC, $this->pathItems);

    return;

//    //build a tickler array that is in the right sort order
//    $newkey = 0;
//    $this->sortedPathItems = [];
//    foreach ($this->pathItems as $key => $pathItem) {
//      $this->sortedPathItems[$newkey] = [
//        'correlation_nid' => $key,
//        ''
//      ];
//    }

  }


  /**
   * Array Access Methods.
   *
   */


  public function offsetExists($offset) {
    return array_key_exists($offset, $this->pathItems);
  }

  public function offsetGet($offset) {
    //TODO: Sanity check - if missing expectation id, go look it up?
    // I put a placeholder warning just for now.
    if (empty($this->pathItems[$offset]['expectation_nid'])) {
      \Drupal::messenger()->addWarning("missing expectation NID");
    }
      return $this->pathItems[$offset];
  }

  public function offsetSet($offset, $value) {
    $this->pathItems[$offset] = $value;
  }

  public function offsetUnset($offset) {
    unset($this->pathItems[$offset]);
  }


  /**
   * IteratorAggregate methods.
   *
   */

  public function getIterator() {
    return new \ArrayIterator($this->pathItems);

  }

  /**
   * My methods.
   *
   */


  /**
   * Get Narrative Citations array.
   *
   * @param $correlation_nid
   *   Correlation nid.
   *
   * @return array
   *  Citation nids array.
   */
  public function getNarrativeCitations(int $correlation_nid) {
    foreach($this->pathItems as $pathItem) {
      $correlation = $this->getCorrelation($correlation_nid);
      if (isset($correlation['narrative']['citations'])) {
        return $correlation['narrative']['citations'];
      }
    }
    return [];
  }


  public function getActivityCitations(int $correlation_nid) {
    if (isset($this->pathItems[$correlation_nid]['activity']['citations'])) {
      return $this->pathItems[$correlation_nid]['activity']['citations'];
    }
    else {
      return [];
    }
  }

  /**
   * is this citation initially open in the accordion?
   *
   * @param int $correlation_nid
   * @param int $citation_nid
   * @param string $citation_type
   *
   * @return string
   *   Returns closed or open.
   */
  public function getAccordionState(int $correlation_nid, int $citation_nid, string $citation_type) {
    $citation = $this->getCorrelation($correlation_nid);
    if (isset($citation[$citation_type]['open_in_accordion'])) {
      $citation_open_in_accordion = $citation[$citation_type]['open_in_accordion'];
      if ($citation_nid === $citation_open_in_accordion) {
        return 'open';
      }
    }
    return 'closed';
  }


  private function getCorrelation(int $correlation_nid) {
    foreach($this->pathItems as $pathItem) {
      if ($pathItem['correlation_nid'] === $correlation_nid) {
        return $pathItem;
      }
    }
  }

  /**
   * Go to next breakout/correlation in the votingPath.
   *
   * @param int $correlation_nid
   */
  public function getNextBreakout(int $correlation_nid) {
    $next = FALSE;
    foreach ($this->pathItems as $pathItem) {
      if ($next === TRUE) {
        // build the link.
        return $pathItem;
      }
      if ($pathItem['correlation_nid'] === $correlation_nid) {
        $next = TRUE;
      }
    }
    return [];
  }

  public function getPreviousBreakout( int $correlation_nid) {
    for ($i = 0; $i < count($this->pathItems); $i++) {
      if ($this->pathItems[$i]['correlation_nid' === $correlation_nid]) {
        if ($i == 0 || $i == 1) {
          return [];
        }
        else {
          return $this->pathItems[$i-1];
        }
      }
    }
  }

}
