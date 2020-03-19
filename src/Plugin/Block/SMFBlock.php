<?php

namespace Drupal\social_media_flyout\Plugin\Block;

use Drupal\Core\Url;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\social_media_flyout\Plugin\SMFProfile\SMFProfilePluginManager;

/**
 * Provides a block to display social media flyouts
 *
 * @Block(
 *   id = "social_media_flyout_block",
 *   admin_label = @Translation("Social Media Flyout")
 * )
 */
class SMFBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The SMFProfile plugin manager.
   * We use this to list and configure the SMFProfile plugins.
   * @var \Drupal\social_media_flyout\Plugin\SMFProfile\SMFProfilePluginManager
   */
  protected $smfProfileManager;

  /**
   * Constructs a new SMFBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\social_media_flyout\Plugin\SMFProfile\SMFProfilePluginManager $smfprofile_manager
   *   The SMFProfile plugin manager service. We're injecting this service so that
   *   we can use it to access the SMFProfile plugins.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SMFProfilePluginManager $smfprofile_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->smfProfileManager = $smfprofile_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // Inject the plugin.manager.smfprofile service that represents our plugin
    // manager as defined in the social_media_flyout.services.yml file.
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.smfprofile')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // build render array
    $build = array();

    // don't cache this block for now, need to build in cachecontexts that reset
    // when a feed has been disabled/enabled
    // attach the SMFFeed library
    // add the custom class to the block
    $build['#cache']['max-age'] = 0;
    $build['#attached']['library'][] = 'social_media_flyout/SMFFeed';
    $build['#attributes'] = array(
      'class' => array('smf-block'),
    );

    $build['smf_links'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('smf-links'),
      ),
    );

    // Get the list of all the SMFProfile plugins defined on the system from the
    // plugin manager. Note that at this point, what we have is *definitions* of
    // plugins, not the plugins themselves.
    $plugin_definitions = $this->smfProfileManager->getDefinitions();

    foreach($plugin_definitions as $plugin_id => $plugin_definition) {
      // To get an instance of a plugin, we call createInstance() on the plugin
      // manager, passing the ID of the plugin we want to load
      $plugin = $this->smfProfileManager->createInstance($plugin_id);
      if($plugin->enabled()) {
        $build['smf_links']["{$plugin->getPluginId()}_link"] = array(
          '#type' => 'link',
          '#title' => '',
          '#url' => Url::fromRoute('social_media_flyout.open_feed', array('smf_plugin' => $plugin->getPluginId())),
          '#attributes' => array(
            'class' => array('use-ajax', 'smf-link', 'smf-link-'.$plugin->getPluginId()),
          ),
        );
      }
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'label_display' => FALSE,
    ];
  }

}
