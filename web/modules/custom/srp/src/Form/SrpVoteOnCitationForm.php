<?php

namespace Drupal\srp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a State Review Panel form.
 */
class SrpVoteOnCitationForm extends FormBase {

  /**
   * @var int
   */
  private static $instanceId;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    //return 'srp_srp_vote_on_citation';
    if (empty(self::$instanceId)) {
      self::$instanceId = 1;
    }
    else {
      self::$instanceId++;
    }

    return 'srp_srp_vote_on_citation' . self::$instanceId;
    //return 'my_form_id_' . self::$instanceId;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $citation_nid = NULL, $program_nid = NULL, $expectation_nid = NULL, $correlation_nid = NULL, $action_verb = NULL, $citation_type_string = NULL, $current_vote_status = NULL) {

    // Set a bunch of variables for redirecting to a page.
    $form_state->set('program_nid', $program_nid);
    $form_state->set('expectation_nid', $expectation_nid);
    $form_state->set('correlation_nid', $correlation_nid);
    $form_state->set('action_verb', $action_verb);
    $form_state->set('citation_type_string', $citation_type_string);
    $form_state->set('citation_nid', $citation_nid);


    $form['actions'] = [
      '#type' => 'actions',
    ];

    // TODO: replace classes: hilited-button and blue-button with
    // Abel sanctioned classes.
    //$current_vote_status: pending, approved, rejected, unknown?
    $attributes = [];
    if (isset($current_vote_status)) {
      if ($current_vote_status === "approved") {
        $attributes = ['class' => ['hilited-button', 'blue-button']];
      }
    }
    $form['actions']['approve'] = [
      '#type' => 'submit',
      '#value' => $this->t("Approve Citation $citation_nid"),
      '#citation_nid' => $citation_nid,
      '#voting_action' => 'Accept',
      '#name' => "approveCitation_$citation_nid",
      '#attributes' => $attributes,
    ];


    //$current_vote_status: pending, approved, rejected, unknown?
    $attributes = [];
    if (isset($current_vote_status)) {
      if ($current_vote_status === "rejected") {
        $attributes = ['class' => ['hilited-button', 'blue-button']];
      }
    }
    $form['actions']['reject'] = [
      '#type' => 'submit',
      '#value' => $this->t("Reject Citation $citation_nid"),
      '#citation_nid' => $citation_nid,
      '#voting_action' => 'Reject',
      '#name' => "rejectCitation_$citation_nid",
      '#attributes' => $attributes,
    ];

    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t("Cancel Citation $citation_nid"),
      '#citation_nid' => $citation_nid,
      '#voting_action' => 'Cancel',
      '#name' => "cancelCitation_$citation_nid",
    ];

    //    $url = Url::fromUri('internal:/reports/search');
    //    $form['vote_approve'] = [
    //      '#type' => 'link',
    //      '#title' => $this->t('Approve Citation'),
    //      '#url' => $url,
    //      '#attributes' => [
    //        'class' => [
    //          'button',
    //        ],
    //      ],
    //    ];
    //
    //
    //    $url = Url::fromUri('internal:/reports/search');
    //    $form['vote_reject'] = [
    //      '#type' => 'link',
    //      '#title' => $this->t('Reject Citation'),
    //      '#url' => $url,
    //      '#attributes' => [
    //        'class' => [
    //          'button',
    //        ],
    //      ],
    //    ];
    //
    //    $url = Url::fromUri('internal:/reports/search');
    //    $form['vote_cancel'] = [
    //      '#type' => 'link',
    //      '#title' => $this->t('Cancel Citation'),
    //      '#url' => $url,
    //      '#attributes' => [
    //        'class' => [
    //          'not-a-button',
    //        ],
    //      ],
    //    ];


    return $form;
  }



  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
//    if (mb_strlen($form_state->getValue('message')) < 10) {
//      $form_state->setErrorByName('name', $this->t('Message should be at least 10 characters.'));
//    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();
    $id = $element['#id'];
    $voting_action = $element['#voting_action'];
    //$citation_nid = $element['#citation_nid'];
    $citation_nid = $form_state->get('citation_nid');
    $program_nid = $form_state->get('program_nid');
    $expectation_nid = $form_state->get('expectation_nid');
    $correlation_nid = $form_state->get('correlation_nid');
    $action_verb = $form_state->get('action_verb');
    $citation_type_string = $form_state->get('citation_type_string');

    if ($voting_action === 'Accept') {
      $this->messenger()->addStatus($this->t("Citation $citation_nid approved!"));
      // TODO: Call vote service..


    }
    else if ($voting_action === 'Reject') {
      $this->messenger()->addStatus($this->t("Citation $citation_nid rejected!"));
    }
    else if ($voting_action === 'Cancel') {
      $this->messenger()->addStatus($this->t("Citation $citation_nid cancelled!"));
    }


    $form_state->setRedirect('srp.correlation.voting', [
      'program_nid' => $program_nid,
      'expectation_nid' => $expectation_nid,
      'correlation_nid' => $correlation_nid,
      'action_verb' =>$action_verb,
      'citation_type_string' => $citation_type_string,
      ],
      ['fragment' => $id],
    );
//    $this->messenger()->addStatus($this->t('The message has been sent.'));
//    $form_state->setRedirect('<front>');
  }

  /**
   * {@inheritdoc}
   */
  public function approveCitation(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('Approved!'));
    $element = $form_state->getTriggeringElement();
    //$form_state->setRedirect('internal:/teks/admin/srp/program/555/expectation/666/correlation/102/vote/narrative');
    //$form_state->setRedirect('<front>');
  }

}
