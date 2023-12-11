<?php

namespace Drupal\contact_us\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;

/**
 * Provides the form for adding contacts.
 */
class ContactForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'contact_us_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $record = NULL) {

    $form['title'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="row"><div class="col-sm-12 contact-us">',
      '#markup' => "<h6>Contact us:</h6>",
    ];

    $form['fullname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name:'),
      '#maxlength' => 20,
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['txt-class'],
      
      ],
      '#default_value' => '',
      '#prefix' => '<div class="row"><div id="div-fullname col-sm-12"><div class="div-fullname-main">',
    
    ];
 
    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email:'),
      '#maxlength' => 50,
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['txt-class'],
      ],
      '#default_value' => '',
    ];

    $form['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone:'),
      '#maxlength' => 11,
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['txt-class'],
      ],
      '#default_value' => '',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['Save'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#ajax' => ['callback' => '::saveDataAjaxCallback'],
      '#value' => $this->t('Submit'),
      '#suffix' => '</div></div></div></div></div>',
    ];

    $form['#attached']['library'][] = 'contact_us/global_styles';
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    $form['#attached']['library'][] = 'contact_us/form-validation';


    return $form;
  }
  /**
   * Our custom Ajax response.
   */
  public function saveDataAjaxCallback(array &$form, FormStateInterface $form_state) {
    $conn = Database::getConnection();
    $values = $form_state->getValues();
    $fields["fullname"] = $values['fullname'];
    $fields["email"] = $values['email'];
    $fields["phone"] = $values['phone'];
    $response = new AjaxResponse();

    // Validate the fields again before proceeding.
    if (empty($fields["fullname"])) {
      $css = ['border' => '1px solid red'];
      $text_css = ['color' => 'red'];
      $message = ($this->t('Full Name not valid.'));

      $response->addCommand(new \Drupal\Core\Ajax\CssCommand('#edit-fullname', $css));
      $response->addCommand(new \Drupal\Core\Ajax\CssCommand('#div-fullname-message', $text_css));
      $response->addCommand(new \Drupal\Core\Ajax\HtmlCommand('#div-fullname-message', $message));
    }
    elseif (empty($fields["email"]) || !\Drupal::service('email.validator')->isValid($fields["email"])) {
      $css = ['border' => '1px solid red'];
      $text_css = ['color' => 'red'];
      $message = ($this->t('Invalid email format.'));
      $response->addCommand(new \Drupal\Core\Ajax\CssCommand('#edit-email', $css));
      $response->addCommand(new \Drupal\Core\Ajax\CssCommand('#div-email-message', $text_css));
      $response->addCommand(new \Drupal\Core\Ajax\HtmlCommand('#div-email-message', $message));
    }
    elseif (empty($fields["phone"]) || !is_numeric($fields["phone"])) {
      $css = ['border' => '1px solid red'];
      $text_css = ['color' => 'red'];
      $message = ($this->t('Phone must be a numeric value.'));
      $response->addCommand(new \Drupal\Core\Ajax\CssCommand('#edit-phone', $css));
      $response->addCommand(new \Drupal\Core\Ajax\CssCommand('#div-phone-message', $text_css));
      $response->addCommand(new \Drupal\Core\Ajax\HtmlCommand('#div-phone-message', $message));
    }
    else {
      // Fields are valid, proceed to save.
      $conn->insert('contacts')
        ->fields($fields)
        ->execute();

      // Clear the form values.
      $form_state->setValue('fullname', '');
      $form_state->setValue('email', '');
      $form_state->setValue('phone', '');

      $render_array = \Drupal::formBuilder()->getForm('Drupal\contact_us\Form\ContactTable', 'All');
      $response->addCommand(new HtmlCommand('.result_message', ''));
      $response->addCommand(new \Drupal\Core\Ajax\AppendCommand('.result_message', $render_array));
      $response->addCommand(new InvokeCommand('.txt-class', 'val', ['']));
      $response->addCommand(new OpenModalDialogCommand("Success!", 'Contact submitted successfully!', ['width' => 400]));
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array & $form, FormStateInterface $form_state) {
    // Handle any additional form submission logic if needed.
  }
}
