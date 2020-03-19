<?php

/**
 * @file
 * Contains \Drupal\social_media_flyout\Form\SMFSettings.
 */

namespace Drupal\social_media_flyout\Form;

use Drupal;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SMFSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'smf_settings_form';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Form constructor
    $form = parent::buildForm($form, $form_state);

    // Default settings
    $config = $this->config('social_media_flyout.settings');

    return $form;
  }

  /**
   * {@inheritdoc}.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    
    $config = $this->config('social_media_flyout.settings');
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * This allows the form to modify settings data
   */
  protected function getEditableConfigNames() {
    return [
      'social_media_flyout.settings',
    ];
  }
}
