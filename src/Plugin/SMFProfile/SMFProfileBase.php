<?php

namespace Drupal\social_media_flyout\Plugin\SMFProfile;

use PDO;
use Drupal\Core\Database\Database;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Url;

use Drupal\Core\Database\Connection;

use Symfony\Component\DependencyInjection\ContainerInterface;

use GuzzleHttp\ClientInterface;

use Drupal\social_media_flyout\Plugin\SMFProfile\SMFProfileInterface;

/**
 * @see \Drupal\social_media_flyout\Annotation\SMFProfile
 * @see \Drupal\social_media_flyout\Plugin\SMFProfile\SMFProfileInterface
 */
abstract class SMFProfileBase extends PluginBase implements SMFProfileInterface {

  /**
   * SMF config data
   *
   * @var Drupal::config
   */
  protected $config;

  /**
   * An http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a Drupal\rest\Plugin\SMFProfileBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ClientInterface $http_client, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->profile_name = $this->pluginDefinition['profile_name'];
    $this->description = $this->pluginDefinition['description'];
    $this->pluginGroup = $this->pluginDefinition['pluginGroup'];
    $this->weight = $this->pluginDefinition['weight'];

    $this->config = \Drupal::config('social_media_flyout.smfprofile');

    $this->httpClient = $http_client;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // Inject the http client service
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client'),
      $container->get('database')
    );
  }

  /**
   * Returns the enabled status of the plugin
   */
  public function enabled() {
    return $this->config->get("{$this->pluginId}_enable");
  }

  /**
   * Returns the plugin profile name
   */
  public function profileName() {
    // Retrieve the @profile_name property from the annotation and return it.
    return $this->pluginDefinition['profile_name'];
  }

  /**
   * Returns the plugin description
   */
  public function description() {
    // Retrieve the @description property from the annotation and return it.
    return $this->pluginDefinition['description'];
  }

  /**
   * Returns the pluginGroup property
   */
  public function pluginGroup() {
    // Retrieve the @pluginGroup property from the annotation and return it.
    return $this->pluginDefinition['pluginGroup'];
  }

  /**
   * Returns the weight property
   */
  public function weight() {
    // Retrieve the @weight property from the annotation and return it.
    return $this->pluginDefinition['weight'];
  }

  /**
   * Returns the post limit config option
   */
  public function getPostLimit() {
    $post_count = $this->config->get("{$this->pluginId}_post_count");
    if(!$post_count || $post_count < 5 || $post_count > 50) $post_count = 15;
    return $post_count;
  }


  /**
   * {@inheritdoc}.
   */
  public function buildFeedHeader() {
    $build = array();

    // create a top wrapper for the title and close button
    $build["{$this->pluginId}_header_top"] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('smf-feed-header-top'),
      ),
    );

    // title of feed
    $build["{$this->pluginId}_header_top"]["{$this->pluginId}_title"] = array(
      '#markup' => '<h3 class="smf-feed-title">'.$this->profileName().' Feed</h3>',
    );

    // close feed link
    $build["{$this->pluginId}_header_top"]["{$this->pluginId}_close_feed"] = array(
      '#type' => 'link',
      '#title' => t('X'),
      '#url' => Url::fromRoute('social_media_flyout.close_feed'),
      '#attributes' => array(
        'class' => array('use-ajax', 'close-feed'),
      ),
    );

    // link to profile
    $build["{$this->pluginId}_link"] = array(
      '#type' => 'link',
      '#title' => $this->profileUser(),
      '#url' => Url::fromUri($this->getProfileUrl(), array('attributes' => array('target' => '_blank'))),
      '#attributes' => array(
        'class' => array('smf-profile-link', $this->pluginId.'-profile-link'),
      ),
    );

    return $build;
  }

  /**
   * {@inheritdoc}.
   */
  public function buildFeed() {
    // get the posts
    $posts = $this->getPosts();

    // build the content array
    $content = array();

    // loop through the posts
    foreach($posts as $post_data) {
      // build post and add to content array
      $content['smf_post_'.$post_data->post_id] = $this->buildPost($post_data);
    }

    // return the content array
    return $content;
  }

  /**
   * {@inheritdoc}.
   */
  public function getPosts() {
    // get the post limit
    $post_limit = $this->getPostLimit();

    // get the posts from the database
    $query = $this->database->select('smf_posts', 'smf')
    ->fields('smf')
    ->condition('plugin_id', $this->pluginId)
    ->condition('profile_user', $this->profileUser())
    ->orderBy('posted', 'DESC')
    ->range(0, $post_limit)
    ->execute();
    $result = $query->fetchAll();

    return $result;
  }

  /**
   * {@inheritdoc}.
   */
  public function getLastPost() {
    $query = $this->database->query("SELECT * FROM {smf_posts} WHERE plugin_id=:plugin_id ORDER BY posted DESC LIMIT 1", array(':plugin_id' => $this->pluginId));
    $result = $query->fetch();
    return $result;
  }

  /**
   * {@inheritdoc}.
   */
  public function fetchPosts() {
    return array();
  }

  /**
   * {@inheritdoc}.
   */
  public function updatePosts() {

  }

  /**
   * {@inheritdoc}.
   */
  public function insertPost($post) {
    // only insert if post doesn't already exist
    $query = $this->database->query("SELECT post_id FROM {smf_posts} WHERE src_post_id=:src_post_id AND plugin_id=:plugin_id", array(':src_post_id' => $post['src_post_id'], ':plugin_id' => $this->pluginId));
    // is this most efficient way of checking?
    $result = $query->fetchAll();
    if(empty($result)) {
      $this->database->insert('smf_posts')
      ->fields(array(
        'src_post_id' => $post['src_post_id'],
        'plugin_id' => $this->pluginId,
        'profile_user' => $this->profileUser(),
        'posted' => $post['posted'],
        'retrieved' => REQUEST_TIME,
        'post_data' => serialize($post['post_data']),
      ))
      ->execute();
    }    
  }

  /**
   * {@inheritdoc}.
   */
  public function form(&$config) {
    // Enable Checkbox
    $form["{$this->pluginId}_enable"] = array(
      '#type' => 'checkbox',
      '#title' => 'Enable '.$this->profileName().' Feed',
      '#default_value' => $config->get("{$this->pluginId}_enable"),
    );

    // Number of posts to display
    $form["{$this->pluginId}_post_count"] = array(
      '#type' => 'number',
      '#min' => 5,
      '#max' => 50,
      '#step' => 5,
      '#title' => 'Number of '.$this->profileName().' Posts',
      '#default_value' => $config->get("{$this->pluginId}_post_count"),
      '#description' => t('The number of posts to show in the feed'),
    );

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
  public function submitForm(array &$form, FormStateInterface $form_state, &$config) {
    $config->set("{$this->pluginId}_enable", $form_state->getValue("{$this->pluginId}_enable"));
    $config->set("{$this->pluginId}_post_count", $form_state->getValue("{$this->pluginId}_post_count"));
  }

}
