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
 *   id = "facebook",
 *   profile_name = "Facebook",
 *   description = @Translation("Facebook Profile"),
 *   pluginGroup = "social_media_flyout.smfprofile",
 *   weight = "10"
 * )
 */
class SMFProfile_Facebook extends SMFProfileBase {

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

    // the Facebook page name
    $form["{$this->pluginId}_api_settings"]["{$this->pluginId}_page_name"] = array(
      '#type' => 'textfield',
      '#title' => t('Facebook Page Name'),
      '#default_value' => $this->config->get("{$this->pluginId}_page_name"),
      '#description' => t("The name of your Facebook page"),
      //'#required' => TRUE,
    );

    // the Facebook application id
    $form["{$this->pluginId}_api_settings"]["{$this->pluginId}_app_id"] = array(
      '#type' => 'textfield',
      '#title' => t('Facebook AppID'),
      '#default_value' => $this->config->get("{$this->pluginId}_app_id"),
      '#description' => t('The ID of your Facebook application.<br /><a href="https://developers.facebook.com/apps/" target="_blank">Create a Facebook APP</a>'),
      //'#required' => TRUE,
    );

    // the Facebook application secret
    $form["{$this->pluginId}_api_settings"]["{$this->pluginId}_app_secret"] = array(
      '#type' => 'textfield',
      '#title' => t('Facebook Secret'),
      '#default_value' => $this->config->get("{$this->pluginId}_app_secret"),
      '#description' => t('The Secret of your Facebook application'),
      //'#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}.
   */
  public function submitForm(array &$form, FormStateInterface $form_state, &$config) {
    parent::submitForm($form, $form_state, $config);
    $config->set("{$this->pluginId}_page_name", $form_state->getValue("{$this->pluginId}_page_name"));
    $config->set("{$this->pluginId}_app_id", $form_state->getValue("{$this->pluginId}_app_id"));
    $config->set("{$this->pluginId}_app_secret", $form_state->getValue("{$this->pluginId}_app_secret"));
  }

  
  /**
   * {@inheritdoc}.
   */
  public function profileUser() {
    return $this->config->get("{$this->pluginId}_page_name");
  }

  /**
   * {@inheritdoc}.
   */
  public function getProfileUrl() {
    return 'https://www.facebook.com/' . $this->config->get("{$this->pluginId}_page_name");
  }

  /**
   * {@inheritdoc}.
   */
  public function buildPost($post) {
    $build = array();

    // unserizalize the raw post data
    $post_data = unserialize($post->post_data);

    // facebook post wrapper with id
    $build[$this->pluginId.'_post_'.$post->src_post_id] = array(
      '#type' => 'container',
      '#id' => $this->pluginId.'-post-'.$post->src_post_id,
      '#attributes' => array(
        'class' => array('smf-post', 'smf-post-'.$post->post_id, $this->pluginId.'-post', $this->pluginId.'-post-'.$post->src_post_id),
      ),
    );

    // facebook post date
    $build[$this->pluginId.'_post_'.$post->src_post_id][$this->pluginId.'_post_date']['#markup'] = '<div class="smf-post-date '.$this->pluginId.'-post-date">'. date('m/d/Y h:ia', $post->posted) .'</div>';

    // facebook post content
    $build[$this->pluginId.'_post_'.$post->src_post_id][$this->pluginId.'_post_content']['#markup'] = '<div class="smf-post-content '.$this->pluginId.'-post-content">'. $post_data->message .'</div>';
    
    return $build;
  }

  /**
   * {@inheritdoc}.
   */
  public function fetchPosts($last_post_id=FALSE) {
    // get the facebook credentials and settings
    $page_name = $this->config->get("{$this->pluginId}_page_name");
    $app_id = $this->config->get("{$this->pluginId}_app_id");
    $app_secret = $this->config->get("{$this->pluginId}_app_secret");

    // if any of the above are not set, don't proceed
    if(
      !$page_name
      || !$app_id
      || !$app_secret
    ) {
      // set a message maybe?
      return array();
    }

    // get an access token first
    $token_url = 'https://graph.facebook.com/oauth/access_token?client_id='.$app_id.'&client_secret='.$app_secret.'&grant_type=client_credentials';
    try {
      $request = $this->httpClient->request('GET', $token_url);
      $status = $request->getStatusCode();
      $access_token_response = $request->getBody()->getContents();
    }
    catch (RequestException $e) {
      // an error occurred
    }
    $access_token_response = json_decode($access_token_response);
    $access_token = $access_token_response->access_token;

    // get the posts from the page
    // see: https://developers.facebook.com/docs/graph-api/using-graph-api/
    // super useful documentation on the available API calls
    $post_limit = $this->getPostLimit();
    $posts_url = 'https://graph.facebook.com/'.$page_name.'?fields=feed.order(chronological).limit('.$post_limit.')';
    // check for the last post and only pull new posts
    $last_post = $this->getLastPost();
    if($last_post) $posts_url .= ".since(".$last_post->posted.')';
    // add the access toke on last
    $posts_url .= '&access_token='.$access_token;
    // check for last post id
    //if($last_post_id) $query_params .= "&since_id=".$last_post_id;
    try {
      $request = $this->httpClient->request('GET', $posts_url);
      $status = $request->getStatusCode();
      $posts_response = $request->getBody()->getContents();
    }
    catch (RequestException $e) {
      // an error occurred
    }
    $posts_data = json_decode($posts_response);

    return $posts_data;
  }

  /**
   * {@inheritdoc}.
   */
  public function updatePosts() {
    // fetch the posts
    $posts = $this->fetchPosts();

    // need to parse the data from the api call and then insert into the
    // database
    foreach($posts->feed->data as $post) {
      // should probably remove raw post data I don't need before passing it in at some
      // point
      $insert_post = array(
        'src_post_id' => $post->id,
        'posted' => strtotime($post->created_time),
        'post_data' => $post,
      );
      $this->insertPost($insert_post);
    }
  }


  

}
