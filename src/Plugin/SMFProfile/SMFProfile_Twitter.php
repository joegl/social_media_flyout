<?php

namespace Drupal\social_media_flyout\Plugin\SMFProfile;

use Drupal\Core\Url;

use Drupal\Core\Form\FormStateInterface;

use Drupal\social_media_flyout\Plugin\SMFProfile\SMFProfilePermBase;

/**
 * @see \Drupal\social_media_flyout\Annotation\SMFProfile
 *
 * Plugin Annotation
 *
 * @SMFProfile(
 *   id = "twitter",
 *   profile_name = "Twitter",
 *   description = @Translation("Twitter Profile"),
 *   pluginGroup = "social_media_flyout.smfprofile",
 *   weight = "10"
 * )
 */
class SMFProfile_Twitter extends SMFProfileBase {

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
    
    // the Twitter screen name
    $form["{$this->pluginId}_api_settings"]["{$this->pluginId}_screen_name"] = array(
      '#type' => 'textfield',
      '#title' => t('Twitter Screen Name'),
      '#default_value' => $this->config->get("{$this->pluginId}_screen_name"),
      '#description' => t("The screen name of your Twitter account"),
      //'#required' => TRUE,
    );

    // the Twitter consumer key
    $form["{$this->pluginId}_api_settings"]["{$this->pluginId}_consumer_key"] = array(
      '#type' => 'textfield',
      '#title' => t('Twitter Consumer Key'),
      '#default_value' => $this->config->get("{$this->pluginId}_consumer_key"),
      '#description' => t('The Consumer Key of your Twitter application.<br /><a href="https://apps.twitter.com/" target="_blank">Create a Twitter APP</a>'),
      //'#required' => TRUE,
    );

    // the Twitter consumer secret
    $form["{$this->pluginId}_api_settings"]["{$this->pluginId}_consumer_secret"] = array(
      '#type' => 'textfield',
      '#title' => t('Twitter Consumer Secret'),
      '#default_value' => $this->config->get("{$this->pluginId}_consumer_secret"),
      '#description' => t('The Consumer Secret of your Twitter application'),
      //'#required' => TRUE,
    );

    // the Twitter access token
    $form["{$this->pluginId}_api_settings"]["{$this->pluginId}_access_token"] = array(
      '#type' => 'textfield',
      '#title' => t('Twitter Access Token'),
      '#default_value' => $this->config->get("{$this->pluginId}_access_token"),
      '#description' => t('The Access Token for your Twitter application.'),
      //'#required' => TRUE,
    );

    // the Twitter access token secret
    $form["{$this->pluginId}_api_settings"]["{$this->pluginId}_access_token_secret"] = array(
      '#type' => 'textfield',
      '#title' => t('Twitter Access Token Secret'),
      '#default_value' => $this->config->get("{$this->pluginId}_access_token_secret"),
      '#description' => t('The Access Token Secret for your Twitter application.'),
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
    $config->set("{$this->pluginId}_consumer_key", $form_state->getValue("{$this->pluginId}_consumer_key"));
    $config->set("{$this->pluginId}_consumer_secret", $form_state->getValue("{$this->pluginId}_consumer_secret"));
    $config->set("{$this->pluginId}_access_token", $form_state->getValue("{$this->pluginId}_access_token"));
    $config->set("{$this->pluginId}_access_token_secret", $form_state->getValue("{$this->pluginId}_access_token_secret"));
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
    return 'https://www.twitter.com/' . $this->config->get("{$this->pluginId}_screen_name");
  }

  /**
   * {@inheritdoc}.
   */
  public function buildPost($post) {
    $build = array();

    // unserizalize the raw post data
    $post_data = unserialize($post->post_data);

    // twitter post wrapper with id
    $build[$this->pluginId.'_post_'.$post->src_post_id] = array(
      '#type' => 'container',
      '#id' => $this->pluginId.'-post-'.$post->src_post_id,
      '#attributes' => array(
        'class' => array('smf-post', 'smf-post-'.$post->post_id, $this->pluginId.'-post', $this->pluginId.'-post-'.$post->src_post_id),
      ),
    );

    // twitter post date
    $build[$this->pluginId.'_post_'.$post->src_post_id][$this->pluginId.'_post_date']['#markup'] = '<div class="smf-post-date '.$this->pluginId.'-post-date">'. date('m/d/Y h:ia', $post->posted) .'</div>';

    // twitter post content
    $build[$this->pluginId.'_post_'.$post->src_post_id][$this->pluginId.'_post_content']['#markup'] = '<div class="smf-post-content '.$this->pluginId.'-post-content">'. $post_data->text .'</div>';

    // twitter post view link
    $build[$this->pluginId.'_post_'.$post->src_post_id][$this->pluginId.'_post_view_link'] = array(
      '#type' => 'link',
      '#url' => Url::fromUri('https://twitter.com/'.$post_data->user->screen_name.'/status/'.$post_data->id, array('attributes' => array('target' => '_blank'))),
      '#title' => t('View Tweet'),
      '#attributes' => array(
        'class' => array('smf-post-view-link', $this->pluginId.'-post-view-link'),
      ),
    );

    return $build;
  }

  /**
   * {@inheritdoc}.
   */
  public function fetchPosts() {
    // get the twitter credentials and settings
    $screen_name = $this->config->get("{$this->pluginId}_screen_name");
    $consumer_key = $this->config->get("{$this->pluginId}_consumer_key");
    $consumer_secret = $this->config->get("{$this->pluginId}_consumer_secret");
    $oauth_access_token = $this->config->get("{$this->pluginId}_access_token");
    $oauth_access_token_secret = $this->config->get("{$this->pluginId}_access_token_secret");

    // if any of the above are not set, don't proceed
    if(
      !$screen_name
      || !$consumer_key
      || !$consumer_secret
      || !$oauth_access_token
      || !$oauth_access_token_secret
    ) {
      // set a message maybe?
      return array();
    }

    // format oauth data to be used for authorization
    // see: https://developer.twitter.com/en/docs/basics/authentication/guides/authorizing-a-request.html
    $oauth = array(
      'oauth_consumer_key' => $consumer_key,
      'oauth_nonce' => REQUEST_TIME,
      'oauth_signature_method' => 'HMAC-SHA1',
      'oauth_token' => $oauth_access_token,
      'oauth_timestamp' => REQUEST_TIME,
      'oauth_version' => '1.0'
    );

    // setup our specific API call
    $base_url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
    $query_params = '?count=25&screen_name='.$screen_name;
    $requestMethod = 'GET';

    // if a post id was passed, only pull posts made since that post
    $last_post = $this->getLastPost();
    if($last_post) $query_params .= "&since_id=".$last_post->src_post_id;

    // add all query params to the oauth data, as it needs to be used to build
    // the authorization header
    if(!is_null($query_params)) {
      $all_params = str_replace('?', '', explode('&', $query_params));
      foreach ($all_params as $g) {
        $split = explode('=', $g);
        /** In case a null is passed through **/
        if (isset($split[1])) {
          $oauth[$split[0]] = urldecode($split[1]);
        }
      }
    }

    // build oauth data and headers
    // Twitter Oauth wants us to combine, hash, encode, and do this preprocess
    // some of the keys and secrets before sending
    // see: https://developer.twitter.com/en/docs/basics/authentication/guides/authorizing-a-request.html
    // right now I am using a more advanced version of twitter API, that needs
    // extra credentials for the user to act on behalf of the user. This module
    // is simply building a feed, so we might only need more basic oauth
    // without the user secrets, because we won't need to act on behalf of the
    // user
    // see: https://developer.twitter.com/en/docs/basics/authentication/overview/application-only
    // Specifically "application-only" authentication 
    // The url here should be the base URL without any query string params
    $oauth_params = array();
    ksort($oauth);
    foreach($oauth as $key => $value) {
      $oauth_params[] = rawurlencode($key) . '=' . rawurlencode($value);
    }
    $base_info = $requestMethod . "&" . rawurlencode($base_url) . '&' . rawurlencode(implode('&', $oauth_params));
    $composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);
    $oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
    $oauth['oauth_signature'] = $oauth_signature;

    // build the authorization header from the oauth data
    $header = 'OAuth ';
    $header_values = array();
    foreach($oauth as $key => $value) {
      if(in_array($key, array('oauth_consumer_key', 'oauth_nonce', 'oauth_signature',
          'oauth_signature_method', 'oauth_timestamp', 'oauth_token', 'oauth_version'))) {
          $header_values[] = "$key=\"" . rawurlencode($value) . "\"";
      }
    }
    $header .= implode(', ', $header_values);

    // combine the base url with query paramaters to make the api call 
    // IMPORTANT: The oauth data and headers should be built with the URL and
    // query paramaters separated; then they should be combined to issue the 
    // actual GET request
    $call_url = $base_url . $query_params;

    // add the oauth authorization headers and any other request parameters
    $request_options = array(
      'content-type' => 'application/x-www-form-urlencoded',
      'headers' => array(
        'Authorization' => $header,
      ),
    );

    // make our request
    try {
      $request = $this->httpClient->request($requestMethod, $call_url, $request_options);
      $status = $request->getStatusCode();
      $data = $request->getBody()->getContents();
    }
    catch (RequestException $e) {
      //An error happened.
    }

    // decode the json data
    $data = json_decode($data);

    return $data;
  }

  /**
   * {@inheritdoc}.
   */
  public function updatePosts() {
    // fetchposts
    $posts = $this->fetchPosts();

    // need to parse the data from the api call and then insert into the
    // database
    foreach($posts as $post) {
      // should probably remove raw post data I don't need before passing it in at some
      // point
      $insert_post = array(
        'src_post_id' => $post->id,
        'posted' => strtotime($post->created_at),
        'post_data' => $post,
      );
      $this->insertPost($insert_post);
    }
  }

}
