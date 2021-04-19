<?php

namespace Drupal\srp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
//use Drupal\srp\VotingProcessor;
use Drupal\srp\VotingProcessorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a State Review Panel form.
 */
class SrpVoteOnCitationForm extends FormBase {

  /**
   * @var int
   */
  private static int $instanceId;

  /**
   * @var VotingProcessorInterface $voting_processor
   */
  protected VotingProcessorInterface $voting_processor;

  public function __construct(VotingProcessorInterface $votingProcessor) {
    $this->voting_processor = $votingProcessor;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('srp.vote_processor')
    );
  }


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

    // selected class will mean this one is selected
    //$current_vote_status: pending, accepted, rejected, unknown?
    $attributes = [];
    if (isset($current_vote_status)) {
      if ($current_vote_status === "accepted") {
        $attributes = ['class' => ['hilited-button', 'blue-button', 'selected']];
      }
    }
    $form['actions']['accept'] = [
      '#type' => 'submit',
      '#value' => $this->t("Accept Citation $citation_nid"),
      '#citation_nid' => $citation_nid,
      '#voting_action' => 'Accept',
      '#name' => "acceptCitation_$citation_nid",
      '#attributes' => $attributes,
    ];


    //$current_vote_status: pending, accepted, rejected, unknown?
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


    $form['reject_radio'] = [
      '#type' => 'radios',
      '#title' => $this->t('Pick a colour'),
      '#options' => [
        'blue' => $this->t('Blue'),
        'white' => $this->t('White'),
        'black' => $this->t('Black'),
        'other' => $this->t('Other'),
      ],
      '#attributes' => [
        //define static name and id so we can easier select it
        // 'id' => 'colour_select',
        'name' => 'colour_select'. $citation_nid,
      ],
    ];





    // Quick example of a conditional field

    //create a list of radio boxes that will toggle the  textbox
    //below if 'other' is selected
    $form['colour_select'] = [
      '#type' => 'radios',
      '#title' => $this->t('Pick a colour'),
      '#options' => [
        'blue' => $this->t('Blue'),
        'white' => $this->t('White'),
        'black' => $this->t('Black'),
        'other' => $this->t('Other'),
      ],
      '#attributes' => [
        //define static name and id so we can easier select it
        // 'id' => 'colour_select',
        'name' => 'colour_select'. $citation_nid,
      ],
    ];

    //this textfield will only be shown when the option 'Other'
    //is selected from the radios above.

    //$input_string = ':input[name="colour_select"]';
    $input_string = ':input[name="colour_select' . $citation_nid . '"]';

    $form['custom_colour'] = [
      '#type' => 'textfield',
      '#size' => '60',
      '#placeholder' => 'Enter favourite colour',
      '#attributes' => [
        'id' => 'custom-colour',
      ],
      '#states' => [
        //show this textfield only if the radio 'other' is selected above
        'visible' => [
          //don't mistake :input for the type of field. You'll always use
          //:input here, no matter whether your source is a select, radio or checkbox element.
          //':input[name="colour_select"]' => ['value' => 'other'],
          $input_string => ['value' => 'other'],
        ],
      ],
    ];






    //    $url = Url::fromUri('internal:/reports/search');
    //    $form['vote_accepte'] = [
    //      '#type' => 'link',
    //      '#title' => $this->t('Accept Citation'),
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
      $this->messenger()->addStatus($this->t("Citation $citation_nid accepted!"));

//      $voting_service = \Drupal::service('srp.vote_processor');
//      $voting_service->voteOnCitation($citation_nid);
      $this->voting_processor->voteOnCitation($citation_nid, "Accepted");

    }
    else if ($voting_action === 'Reject') {
      $this->voting_processor->voteOnCitation($citation_nid, "Rejected");
      $this->messenger()->addStatus($this->t("Citation $citation_nid rejected!"));
    }
    else if ($voting_action === 'Cancel') {
      $this->messenger()->addStatus($this->t("Citation $citation_nid cancelled!"));
      $this->voting_processor->cancelVoteOnCitation($citation_nid);

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


}
