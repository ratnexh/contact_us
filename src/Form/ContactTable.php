<?php
namespace Drupal\contact_us\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides the list of contacts.
 */
class ContactTable extends FormBase {
	
	 /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dn_contact_table_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state,$pageNo = NULL) {
    

   //$pageNo = 2;
   $form['title']=[
    '#type'=>'markup',
    '#markup' => "<h6>Contact us List</h6>",
   ];

    $header = [
      'id' => $this->t('No'),
      'fullname' => $this->t('Full Name'),
	  'email' => $this->t('Email'),
	  'phone'=> $this->t('Phone'),	  
	
    ];

   if($pageNo != ''){
    $form['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $this->get_contact
($pageNo),
      '#empty' => $this->t('No records found'),
    ];
   }else{
	    $form['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $this->get_contact
("All"),
      '#empty' => $this->t('No records found'),
    ];
   }
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
  	$form['#attached']['library'][] = 'contact_us/global_styles';
	
     $form['#theme'] = 'contact_form';
	   $form['#prefix'] = '<div class="result_message">';
	   $form['#suffix'] = '</div>';

     $form['#cache'] = [
      'max-age' => 0
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array & $form, FormStateInterface $form_state) {	}
  
 function get_contact($opt) {
 $res = array();
 if($opt == "All"){
  $results = \Drupal::database()->select('contacts', 'st');
  $results->fields('st');
  $results->range(0, 15);
  $results->orderBy('st.id','desc');
  $res = $results->execute()->fetchAll();
  $ret = [];
 }else{
	$query = \Drupal::database()->select('contacts', 'st');
  $query->fields('st');
  $query->range($opt*15, 15);
  $query->orderBy('st.id','DESC');
  $res = $query->execute()->fetchAll();
  $ret = [];
 }
    foreach ($res as $row) { 
	  $edit = Url::fromUserInput('/edit/' . $row->id);
	  $delete = Url::fromUserInput('/delete/' . $row->id);  
	  $edit_link = Link::fromTextAndUrl(t('Edit'), $edit);
	  $delete_link = Link::fromTextAndUrl(t('Delete'), $delete);
	  $edit_link = $edit_link->toRenderable();
    $delete_link  = $delete_link->toRenderable();
	  $edit_link['#attributes'] = ['class'=>'use-ajax'];
	  $delete_link['#attributes'] = ['class'=>'use-ajax'];
    $mainLink = t('@linkApprove  @linkReject', array('@linkApprove' => $edit_link, '@linkReject' => $delete_link));
    $ret[] = [
    'id' => $row->id,
    'fullname' => $row->fullname,
		'email' => $row->email,
		'phone' => $row->phone,

    'opt1' => \Drupal::service('renderer')->render($edit_link),
    'opt' => \Drupal::service('renderer')->render($delete_link), 
    ];
    }
    return $ret;
}
	
}