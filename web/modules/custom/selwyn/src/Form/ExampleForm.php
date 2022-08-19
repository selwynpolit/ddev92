<?php

namespace Drupal\selwyn\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a selwyn form.
 */
class ExampleForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'selwyn_example';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#required' => TRUE,
    ];

    $citation_nid = 123456;
    $location_description="";
    $form['location_description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description of Specific Location'),
      '#rows' => 5,
      '#cols' => 50,
      '#resizable' => "both",
//      '#value' => $location_description,
//      '#attributes' => [
//        'id' => "location_description_$citation_nid",
//        'name' => 'location-description-' . $citation_nid,
//      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (mb_strlen($form_state->getValue('message')) < 10) {
      $form_state->setErrorByName('name', $this->t('Message should be at least 10 characters.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();

    $message = $values['message'];
    $location_description = $values['location_description'];
//    $this->messenger()->addStatus($this->t("Message: $message"));
    $this->messenger()->addStatus($this->t("location description: $location_description"));


    return;

    $this->messenger()->addStatus($this->t('The message has been sent.'));
    $form_state->setRedirect('<front>');
  }

}
