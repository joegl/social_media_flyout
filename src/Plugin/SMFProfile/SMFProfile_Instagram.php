<?php

namespace Drupal\social_media_flyout\Plugin\SMFProfile;

use Drupal\Core\Form\FormStateInterface;

use Drupal\social_media_flyout\Plugin\SMFProfile\SMFProfilePermBase;

/**
 * @see \Drupal\social_media_flyout\Annotation\SMFProfile
 *
 * Plugin Annotation
 *
 * @SMFProfile(
 *   id = "instagram",
 *   profile_name = "Instagram",
 *   description = @Translation("Instagram Profile"),
 *   pluginGroup = "social_media_flyout.smfprofile",
 *   weight = "10"
 * )
 */
class SMFProfile_Instagram extends SMFProfileBase {

  /**
   * Constructs a SMFProfile object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  // public function __construct(array $configuration, $plugin_id, $plugin_definition) {
  //   parent::__construct($configuration, $plugin_id, $plugin_definition);
  // }

  /**
   * {@inheritdoc}.
   */
  public function form(&$config) {
    $form = parent::form($config);

    // enter API credentials
    $form["{$this->pluginId}_api_settings"] = array(
      '#type' => 'details',
      '#title' => t('API Settings'),
    );

    // the Instagram screen name
    $form["{$this->pluginId}_api_settings"]["{$this->pluginId}_screen_name"] = array(
      '#type' => 'textfield',
      '#title' => t('Instagram Screen Name'),
      '#default_value' => $this->config->get("{$this->pluginId}_screen_name"),
      '#description' => t("The screen name of your Instagram account"),
      //'#required' => TRUE,
    );

    // the Instagram client id
    $form["{$this->pluginId}_api_settings"]["{$this->pluginId}_client_id"] = array(
      '#type' => 'textfield',
      '#title' => t('Instagram Client ID'),
      '#default_value' => $this->config->get("{$this->pluginId}_client_id"),
      '#description' => t('The Client ID of your Instagram application.<br /><a href="https://instagram.com/developer/clients/manage/" target="_blank">Register a new Instagram APP Client</a>'),
      //'#required' => TRUE,
    );

    // the Instagram client secret
    $form["{$this->pluginId}_api_settings"]["{$this->pluginId}_client_secret"] = array(
      '#type' => 'textfield',
      '#title' => t('Instagram Client Secret'),
      '#default_value' => $this->config->get("{$this->pluginId}_client_secret"),
      '#description' => t('The Client Secret of your Instagram application'),
      //'#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}.
   */
  public function submitForm(array &$form, FormStateInterface $form_state, &$config) {
    parent::submitForm($form, $form_state, $config);
    $config->set("{$this->pluginId}_screen_name", $form_state->getValue("{$this->pluginId}_screen_name"));
    $config->set("{$this->pluginId}_client_id", $form_state->getValue("{$this->pluginId}_client_id"));
    $config->set("{$this->pluginId}_client_secret", $form_state->getValue("{$this->pluginId}_client_secret"));
  }

  /**
   * {@inheritdoc}.
   */
  public function profileUser() {
    return $this->config->get("{$this->pluginId}_screen_name");
  }

  /**
   * {@inheritdoc}.
   */
  public function getProfileUrl() {
    return 'https://www.instagram.com/' . $this->config->get("{$this->pluginId}_screen_name");
  }

  /**
   * {@inheritdoc}.
   */
  public function buildPost($post_data) {
    $content = array();

    return $content;
  }

}
