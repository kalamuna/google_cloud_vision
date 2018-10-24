<?php
namespace Drupal\google_cloud_vision\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class GoogleCloudVisionSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'google_cloud_vision_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'google_cloud_vision.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('google_cloud_vision.settings');
    $form['key'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Name of Key Machine Name'),
      '#description' => $this->t('The machine name of the google api key you stored in using the "Key" module.'),
      '#default_value' => $config->get('key'),
    );
    $form['project_id'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Google Project Id'),
      '#description' => $this->t('The Google Cloud project ID associated with your API key.'),
      '#default_value' => $config->get('project_id'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $keyName = $form_state->getValue('key');
    if (!\Drupal::service('key.repository')->getKey($keyName)) {
      $form_state->setErrorByName('key', t('The key provided is not valid. Please check the machine name of the key you provided from the key module.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
      // Retrieve the configuration
       $this->configFactory->getEditable('google_cloud_vision.settings')
      // Set the submitted configuration setting
      ->set('key', $form_state->getValue('key'))
      ->set('project_id', $form_state->getValue('project_id'))
      // Save the settings.
      ->save();

    parent::submitForm($form, $form_state);
  }
}
