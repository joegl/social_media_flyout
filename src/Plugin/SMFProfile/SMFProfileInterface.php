<?php

namespace Drupal\social_media_flyout\Plugin\SMFProfile;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * An interface for all SMFProfile type plugins.
 *
 * When defining a new plugin type you need to define an interface that all
 * plugins of the new type will implement. This ensures that consumers of the
 * plugin type have a consistent way of accessing the plugin's functionality. It
 * should include access to any public properties, and methods for accomplishing
 * whatever business logic anyone accessing the plugin might want to use.
 *
 * For example, an image manipulation plugin might have a "process" method that
 * takes a known input, probably an image file, and returns the processed
 * version of the file.
 *
 */
interface SMFProfileInterface extends ContainerFactoryPluginInterface {

  /**
   * Return the enabled status of the plugin.
   *
   * @return boolean
   */
  public function enabled();

  /**
   * Returns the plugin profile name (e.g., facebook, twitter, etc.,)
   */
  public function profileName();

  /**
   * Provide a description of the SMFProfile plugin.
   *
   * @return string
   *   A string description of the SMFProfile plugin.
   */
  public function description();

  /**
   * pluginGroup method.
   *
   * @return string
   *   A group string to help organize plugins in the config
   */
  public function pluginGroup();

  /**
   * weight method.
   *
   * @return string
   *   The plugin weight
   */
  public function weight();

  /**
   * Returns the prolfie user/page/screen name
   */
  public function profileUser();

  /**
   * Returns the post limit config option
   */
  public function getPostLimit();

  /**
   * Returns a url to link to the profile
   */
  public function getProfileUrl();

  /**
   * Build the render array for the feed header
   */
  public function buildFeedHeader();

  /**
   * Build the render array for the feed
   */
  public function buildFeed();

  /**
   * Build the render array for an individual post
   */
  public function buildPost($post_data);

  /**
   * Retrieve the posts for the profile from the database
   */
  public function getPosts();

  /**
   * Retrieve the most recent post from the database
   * For determining which posts, if any, to pull next from the API
   */
  public function getLastPost();

  /**
   * Fetch the posts for the profile from the profile's API
   */
  public function fetchPosts();

  /**
   * Find the latest posts and update the database
   */
  public function updatePosts();

  /**
   * Insert a post into the database
   *
   * $post_data should be array with the following keys:
   * - src_post_id
   * - posted
   * - post_data
   */
  public function insertPost($post);

}
