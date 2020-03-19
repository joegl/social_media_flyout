<?php

namespace Drupal\social_media_flyout\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a SMFProfile annotation object.
 *
 * For adding different social media profile feeds to the social media flyout
 *
 * @see \Drupal\social_media_flyout\SMFProfilePluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class SMFProfile extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * A brief, human readable, name of the SMFProfile.
   *
   * This property is designated as being translatable because it will appear
   * in the user interface. This provides a hint to other developers that they
   * should use the Translation() construct in their annotation when declaring
   * this property.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $profile_name;

  /**
   * A brief, human readable, description of the SMFProfile.
   *
   * This property is designated as being translatable because it will appear
   * in the user interface. This provides a hint to other developers that they
   * should use the Translation() construct in their annotation when declaring
   * this property.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * A group string to help organize plugins in the config
   *
   * @var string
   */
  public $pluginGroup;

  /**
   * The plugin weight
   *
   * @var string
   */
  public $weight;

}
