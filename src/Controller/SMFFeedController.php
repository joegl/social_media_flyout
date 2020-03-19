<?php

/**
 * @file
 * Contains \Drupal\social_media_flyout\Controller\SMFFeedController.
 */

namespace Drupal\social_media_flyout\Controller;

use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;


class SMFFeedController extends ControllerBase {

  /**
   * Open a feed in a flyout
   */
  public static function openFeed(Request $request, $smf_plugin=FALSE) {
    // create an ajaxresponse
    $response = new AjaxResponse();

    // load the plugin, create an instance, build a feed and pass to the open 
    // feed command
    $smfProfileManager = \Drupal::service('plugin.manager.smfprofile'); 
    $smf_profile = $smfProfileManager->createInstance($smf_plugin);

    // build a wrapper for the feed
    $build["{$smf_profile->getPluginId()}_wrapper"] = array(
      '#type' => 'container',
    );

    // build the feed header
    $build["{$smf_profile->getPluginId()}_wrapper"]["{$smf_profile->getPluginId()}_header"] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('smf-feed-header'),
      ),
    );
    $build["{$smf_profile->getPluginId()}_wrapper"]["{$smf_profile->getPluginId()}_header"]['header'] = $smf_profile->buildFeedHeader();

    // build the feed content
    $build["{$smf_profile->getPluginId()}_wrapper"]["{$smf_profile->getPluginId()}_feed"] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('smf-feed-content'),
      ),
    );
    $build["{$smf_profile->getPluginId()}_wrapper"]["{$smf_profile->getPluginId()}_feed"]['feed'] = $smf_profile->buildFeed();

    // open the feed and add the content for the profile
    $response->addCommand(new InvokeCommand('#smf_flyout', 'addClass', array('show-flyout')));
    $response->addCommand(new HtmlCommand('#smf_flyout', $build));

    return $response;
  }

  /**
   * Close the flyout
   */
  public static function closeFeed(Request $request) {
    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand('#smf_flyout', 'removeClass', array('show-flyout')));
    return $response;
  }
     
}