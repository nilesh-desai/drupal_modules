<?php

namespace Drupal\axe_act\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\system\Form\SiteInformationForm;

/**
 * Class ExtendSiteInformationForm.
 */
class ExtendSiteInformationForm extends SiteInformationForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $site_config = $this->config('system.site');
    $key_value = $site_config->get('siteapikey');
    $form['site_information']['siteapikey'] = [
      '#type' => 'textfield',
      '#title' => t('Site API Key'),
      '#default_value' => $site_config->get('siteapikey') ?: 'No API Key yet',
    ];

    // Update submit button label.
    if (!empty($key_value)) {
      $form['actions']['submit']['#value'] = t('Update Configurations');
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $api_key = $form_state->getValue('siteapikey');
    $site_config = $this->config('system.site');
    $key_default_value = $site_config->get('siteapikey');
    $this->config('system.site')
      ->set('siteapikey', $api_key)
      ->save();
    parent::submitForm($form, $form_state);

    if ($key_default_value != $api_key) {
      // Set success message.
      drupal_set_message(t('Site API Key has been stored successfully.'), 'status', TRUE);
    }
  }

}
