<?php

namespace Drupal\srp\Controller;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\srp\VotingPath;


/**
 * Returns responses for State Review Panel routes.
 */
class VoteController extends ControllerBase {

  const TESTING = TRUE;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The VotingPath Object.
   *
   * @var \Drupal\srp\VotingPath
   */
  protected VotingPath $votingPath;


  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(AccountInterface $current_user, MessengerInterface $messenger) {
    $this->currentUser = $current_user;
    $this->messenger = $messenger;

    // TODO: go look in sharedtempstore for my votingpath
    // TODO: if none, how do I build the path?

    $this->loadPath();
    return;



  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('messenger')
    );
  }

  private function loadPath() {

    // TODO: Load the path
    // check if there is a path..in tempstore - by program_id


    // expectation nid 1 . (correlation) sort_order 1. correlation nid 1
    //  5555.1
    //  5555.2
    //  5555.3
    //

    // PathItems[]

    if (self::TESTING) {
      $path_item = [];
      $path_items = [];

      $path_item = [
        'correlation_nid' => 100,
        'status' => '',
        'expectation_nid' => 666,  //sp we know we are moving to the next expec.
        'sort_order'=> 1,
        'narrative' => [
          'citations' => [10001,10002,10003],
          'open_in_accordion' => 10001,
          'status' => 'accepted',
        ],
        'activity' => [
          'citations' => [10004,10005,10006],
          'open_in_accordion' => 10001,
          'status' => '',
        ],
      ];
      $path_items[] = $path_item;

      $path_item['correlation_nid'] = 101;
      //$path_item['expectation_nid'] = 666;
      $path_item['sort_order'] = 3;
      $path_item['status'] = '';
      // Not required - so shouldn't fail if not set.
      //$path_item['display'] = 'closed';
      $path_item['narrative']['citations'] = [10101,10102,10103];
      $path_item['narrative']['open_in_accordion'] = 10101;
      $path_item['activity']['citations'] = [10104,101005,10106];
      $path_item['activity']['open_in_accordion'] = 10104;
      $path_item['narrative']['status'] = '';
      $path_item['activity']['status'] = '';
      $path_items[] = $path_item;

      $path_item['correlation_nid'] = 102;
      $path_item['expectation_nid'] = 666;
      $path_item['sort_order'] = 2;
      $path_item['status'] = '';
      $path_item['display'] = 'closed';
      $path_item['narrative']['open_in_accordion'] = 10201;
      $path_item['narrative']['citations'] = [10201,10202,10203];
      $path_item['activity']['citations'] = [10204,10205,10206];
      $path_item['narrative']['status'] = '';
      $path_item['activity']['status'] = '';
      $path_items[] = $path_item;

      $path_item['correlation_nid'] = 103;
      $path_item['expectation_nid'] = 777;
      $path_item['sort_order'] = 1;
      $path_item['status'] = '';
      $path_item['display'] = 'closed';
      $path_item['narrative']['citations'] = [10301,10302,10303];
      $path_item['activity']['citations'] = [10304,10305,10306];
      $path_item['narrative']['status'] = '';
      $path_item['activity']['status'] = '';
      $path_items[99] = $path_item;

      $this->votingPath = new VotingPath($path_items);

//      $blah = $this->votingPath[100];

    }
  }


  // test with
  // https://ddev92.ddev.site/teks/admin/srp/program/555/expectation/666/correlation/102/vote/narrative



  public function build($program_nid, $expectation_nid, $correlation_nid, $action_verb, $citation_type_string) {

    //TODO load the program_node and display program specific info
    //$program_node = Node::load($program_nid);

    //TODO: load the expectation_node and display expectation specific info
    //$expectation_node = Node::load($expectation_nid);

    //TODO: load the correlation_node and display correlation specific info
    //$correlation_node = Node::load($correlation_nid);

    if ($action_verb === 'vote') {
      if ($citation_type_string === 'narrative') {
        $build['correlation']['title'] = [
          '#type' => 'item',
          '#markup' => $this->t('Voting on narrative citation'),
        ];

        /**
         * Loop thru citations.
         */
        $citation_nids = $this->votingPath->getNarrativeCitations($correlation_nid);
        foreach ($citation_nids as $citation_nid) {

          $accordion_state = $this->votingPath->getAccordionState($correlation_nid, $citation_nid, $citation_type_string);
          $build['citation'][$citation_nid]['title'] = [
            '#type' => 'item',
            '#markup' => $this->t("$accordion_state: $citation_type_string citation $citation_nid title, display "),
          ];

          $current_vote_status = "pending";
          //TODO: load my vote from the DB.
          //TODO: load everyone's votes from the db - in the citation node.

          if (self::TESTING) {
            if ($correlation_nid == 102 && $citation_nid == 10201) {
              $current_vote_status = "approved"; //pending, approved, rejected, unknown?
            }
          }

          $voting_form = \Drupal::formBuilder()->getForm('Drupal\srp\Form\SrpVoteOnCitationForm', $citation_nid, $program_nid, $expectation_nid, $correlation_nid, $action_verb, $citation_type_string, $current_vote_status);
          $build['citation'][$citation_nid]['voting_form'] = $voting_form;
        }



        // Next breakout link
        $next_correlation = $this->votingPath->getNextBreakout($correlation_nid);
        if (!empty($next_correlation)) {
          $next_correlation_nid = $next_correlation['correlation_nid'];
          $path = "/teks/admin/srp/program/$program_nid/expectation/$expectation_nid/correlation/$next_correlation_nid/vote/narrative";
          $url = Url::fromUri("internal:$path");
          $build['content']['next_breakout'] = [
            '#title' => "Next breakout >>",
            '#type' => 'link',
            '#url' => $url,
          ];
        }
        else {
          $next_correlation_nid = 0;
          //          $build['content']['next_breakout']['#disabled'] = TRUE;
          //          $build['content']['next_breakout'][0]['value']['#attributes']['disabled'] = 'disabled';
          //          $build['content']['next_breakout']['#access'] = FALSE;
          $build['content']['next_breakout'] = [
            '#title' => "No Next breakout >>",
            '#type' => 'item',
          ];

        }
      }

      // Previous breakout

      // Narrative <---

      // Activity  -->


    }




    $build['content']['zzstuff'] = [
      '#type' => 'item',
      '#markup' => $this->t('It worksz!!!!'),
    ];

    return $build;
  }

}
