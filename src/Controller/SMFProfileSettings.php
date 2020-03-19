<?php

namespace Drupal\social_media_flyout\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\social_media_flyout\Plugin\SMFProfile\SMFProfilePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Controller for our Plugin Manager Settings Page.
 */
class SMFProfileSettings extends ConfigFormBase {

  /**
   * The SMFProfile plugin manager.
   * We use this to list and configure the SMFProfile plugins.
   * @var \Drupal\social_media_flyout\Plugin\SMFProfile\SMFProfilePluginManager
   */
  protected $smfProfileManager;

  /**
   * Constructor.
   * @param \Drupal\social_media_flyout\Plugin\SMFProfile\SMFProfilePluginManager $smfprofile_manager
   *   The SMFProfile plugin manager service. We're injecting this service so that
   *   we can use it to access the SMFProfile plugins.
   */
  public function __construct(SMFProfilePluginManager $smfprofile_manager) {
    $this->smfProfileManager = $smfprofile_manager;
  }

  /**
   * {@inheritdoc}
   *
   * Override the parent method so that we can inject our SMFProfile plugin
   * manager service into the controller.
   *
   * @see container
   */
  public static function create(ContainerInterface $container) {
    // Inject the plugin.manager.smfprofile service that represents our plugin
    // manager as defined in the social_media_flyout.services.yml file.
    return new static($container->get('plugin.manager.smfprofile'));
  }



  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'smfprofile_settings_form';
  }

  /**
   * Displays a page with an overview of our plugin type and plugins.
   *
   * Lists all the SMFProfile plugin definitions by using methods on the
   * \Drupal\social_media_flyout\Plugin\SMFProfile\SMFProfilePluginManager class. Lists out the
   * description for each plugin found by invoking methods defined on the
   * plugins themselves. You can find the plugins we have defined in the
   * \Drupal\social_media_flyout\Plugin\SMFProfile namespace.
   *
   * @return array
   *   Render API array with content 
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor
    $form = parent::buildForm($form, $form_state);
    
    // Default settings
    $config = $this->config('social_media_flyout.smfprofile');

    // Get the list of all the SMFProfile plugins defined on the system from the
    // plugin manager. Note that at this point, what we have is *definitions* of
    // plugins, not the plugins themselves.
    $plugin_definitions = $this->smfProfileManager->getDefinitions();

    foreach($plugin_definitions as $plugin_id => $plugin_definition) {
      // To get an instance of a plugin, we call createInstance() on the plugin
      // manager, passing the ID of the plugin we want to load

      $plugin = $this->smfProfileManager->createInstance($plugin_id);

      // ignore plugin groups and return all instances for now
      // Plugins Fieldset
      $form[$plugin_id] = array(
        '#type' => 'details',
        '#title' => $plugin->profileName(),
        '#open' => TRUE,
      );

      // Get the config options from the plugin itself
      $form[$plugin_id]['config'] = $plugin->form($config);
    }

    return $form;
  }

  /**
   * {@inheritdoc}.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $plugin_definitions = $this->smfProfileManager->getDefinitions();
    foreach ($plugin_definitions as $plugin_id => $plugin_definition) {
      $plugin = $this->smfProfileManager->createInstance($plugin_id);
      $plugin->validateForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('social_media_flyout.smfprofile');

    $plugin_definitions = $this->smfProfileManager->getDefinitions();
    foreach ($plugin_definitions as $plugin_id => $plugin_definition) {
      $plugin = $this->smfProfileManager->createInstance($plugin_id);
      $plugin->submitForm($form, $form_state, $config);
    }

    parent::submitForm($form, $form_state, $config);
    $config->save();
  }

  /**
   * This allows the form to modify settings data
   */
  protected function getEditableConfigNames() {
    return [
      'social_media_flyout.smfprofile',
    ];
  }

}
