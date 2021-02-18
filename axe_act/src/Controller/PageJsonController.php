<?php

namespace Drupal\axe_act\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Controller for queue list.
 */
class PageJsonController extends ControllerBase {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Constructs a new CoffeeController object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, RouteMatchInterface $route_match) {
    $this->config = $config_factory;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('current_route_match')
    );
  }

  /**
   * Checks access for a specific request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account) {
    // Check permissions for the page.
    return AccessResult::allowedIf($account->hasPermission('access content') && $this->checkExtendAccess());
  }

  /**
   * To perform extra access check for the page.
   *
   * @return bool
   *   True or False based on access check.
   */
  public function checkExtendAccess() {

    // Fetch data from current route.
    $node = $this->routeMatch->getParameters()->get('node');
    $apiKey = $this->routeMatch->getParameters()->get('apikey');

    // Fetch site api key.
    $siteApiKey = $this->config->get('system.site')->get('siteapikey');

    $access = FALSE;
    if (!empty($node) && !empty($siteApiKey)) {
      $type = $node->getType();
      if ($apiKey == $siteApiKey && ($type == 'page')) {
        $access = TRUE;
      }
    }
    return $access;
  }

  /**
   * Json format for the node.
   */
  public function response() {

    // Fetch node from current route.
    $node = $this->routeMatch->getParameters()->get('node');

    // Prepare array of node data.
    $data = [
      'id' => $node->id(),
      'title' => $node->getTitle(),
      'body' => $node->get('body')->value,
    ];

    // Return json data.
    return new JsonResponse($data);
  }

}
