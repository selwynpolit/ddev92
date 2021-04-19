<?php


namespace Drupal\srp;


interface VotingProcessorInterface {

  public function voteOnCitation(int $citation_nid, string $vote_value);

  public function cancelVoteOnCitation(int $citation_nid);

}
