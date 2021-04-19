<?php

namespace Drupal\srp;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;


class VotingProcessor implements VotingProcessorInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected EntityTypeManager $entityTypeManager;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected AccountInterface $account;

  /**
   * VotingProcessor constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   * @param \Drupal\Core\Session\AccountInterface $account
   */
  public function __construct(EntityTypeManager $entityTypeManager, AccountInterface $account) {
    $this->entityTypeManager = $entityTypeManager;
    $this->account = $account;
  }


  public function voteOnCitation(int $citation_nid, string $vote_value) {
    $voting_node = $this->loadVotingRecord($citation_nid);
    if (!$voting_node) {
      //New vote.
      $user_name = $this->account->getAccountName();
      $voting_node = Node::create([
        'type' => 'srp_voting_record',
        'title' => "vote on citation $citation_nid for user: $user_name ",
        'field_vote' => $vote_value,
        'field_citation' => ['target_id' => $citation_nid],
        'field_voter' => $this->account->id(),
      ]);
      $voting_node->save();
      $this->updateCitationVoteCount($citation_nid, $vote_value);
      return;
    }
    else {
      // Update existing vote if changed vote.
      $vote = $voting_node->get('field_vote')->value;
      if ($vote !== $vote_value) {
        $voting_node->set('field_vote', $vote_value);
        // Reduce the citation totals.
        if ($vote_value === "Accepted") {
          $this->updateCitationVoteCount($citation_nid, "Rejected", TRUE);
        }
        else {
          $this->updateCitationVoteCount($citation_nid, "Accepted", TRUE);
        }
        $this->updateCitationVoteCount($citation_nid, $vote_value);
        $voting_node->save();
      }
    }
  }

  protected function updateCitationVoteCount(int $citation_nid, string $vote_value, bool $cancel_vote = FALSE) {
    $node = Node::load($citation_nid);
    $accepted_votes = $node->get('field_accepted_votes')->value;
    $rejected_votes = $node->get('field_rejected_votes')->value;
    if ($node) {
      if ($vote_value === "Accepted") {
        if ($cancel_vote) {
          $node->set('field_accepted_votes', $accepted_votes - 1);
        }
        else {
          $node->set('field_accepted_votes', $accepted_votes + 1);
        }
      }
      else {
        if ($cancel_vote) {
          $node->set('field_rejected_votes', $rejected_votes - 1);
        }
        else {
          $node->set('field_rejected_votes', $rejected_votes + 1);
        }
      }
      $node->save();
    }

    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'citations_teks_publisher_citatio')
      ->condition('field_voter.entity:user.uid', $user_id)
      ->condition('field_citation', $citation_nid);

    $nids = $query->execute();
    if (!empty($nids)) {
      //$nodes = $storage->loadMultiple($nids);
      $nid = reset($nids);
      $node = Node::load($nid);
    }

  }

  public function cancelVoteOnCitation(int $citation_nid) {
    $voting_node = $this->loadVotingRecord($citation_nid);
    if ($voting_node) {
      $vote_value = $voting_node->get('field_vote')->value;
      $this->updateCitationVoteCount($citation_nid, $vote_value, TRUE);
      $voting_node->delete();
    }
  }


  protected function loadVotingRecord(int $citation_nid) {
    $node = [];
    $user_id = $this->account->id();
    //$storage = $this->entityTypeManager->getStorage('node');
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'srp_voting_record')
      ->condition('field_voter.entity:user.uid', $user_id)
      ->condition('field_citation', $citation_nid);

    $nids = $query->execute();
    if (!empty($nids)) {
      //$nodes = $storage->loadMultiple($nids);
      $nid = reset($nids);
      $node = Node::load($nid);
    }
    return $node;
  }

}
