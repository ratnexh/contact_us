<?php
namespace Drupal\contact_us\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBuilder;

/**
 * Class ContactController.
 *
 * @package Drupal\contact_us\Controller
 */
class ContactController extends ControllerBase {

/**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * The ContactController constructor.
   *
   * @param \Drupal\Core\Form\FormBuilder $formBuilder
   *   The form builder.
   */
  public function __construct(FormBuilder $formBuilder) {
    $this->formBuilder = $formBuilder;
  }
/**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder')
    );
  }
  
  /**
   * {@inheritdoc}
   */
  public function manageContacts() {
	$form['form'] = $this->formBuilder()->getForm('Drupal\contact_us\Form\ContactForm');
	$render_array = $this->formBuilder()->getForm('Drupal\contact_us\Form\ContactTable','All');
	   $form['form1'] = $render_array;
    return $form;
  }
  /**
   * {@inheritdoc}
   * Deletes the given contacts
   */
  public function deleteContactAjax($cid) {
     $res = \Drupal::database()->query("delete from contacts where id = :id", array(':id' => $cid)); 
	   $render_array = $this->formBuilder->getForm('Drupal\contact_us\Form\ContactTable','All');
     $response = new AjaxResponse();
     $response->addCommand(new OpenModalDialogCommand("Success!", 'Contact deleted successfully!', ['width' => 400]));
	   $response->addCommand(new HtmlCommand('.result_message','' ));
	   $response->addCommand(new \Drupal\Core\Ajax\AppendCommand('.result_message', $render_array));
    
    return $response;

  }
   /**
   * {@inheritdoc}
   * edit the given contacts
   */
  public function editContactAjax($cid) {
    $conn = Database::getConnection();
    $query = $conn->select('contacts', 'st');
    $query->condition('id', $cid)->fields('st');
    $record = $query->execute()->fetchAssoc();
  
    $render_array = \Drupal::formBuilder()->getForm('Drupal\contact_us\Form\ContactEditForm', $record);
    $response = new AjaxResponse();
    $response->addCommand(new OpenModalDialogCommand('Edit Form', $render_array, ['width' => '400']));
    $response->headers->addCacheControlDirective('no-cache', true);
    $response->headers->addCacheControlDirective('max-age', 0);
  
    return $response;
  }
  
}

