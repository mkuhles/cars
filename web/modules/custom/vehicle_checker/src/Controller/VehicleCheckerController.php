<?php

namespace Drupal\vehicle_checker\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * An example controller.
 */
class VehicleCheckerController extends ControllerBase {


  public function __construct(private EntityTypeManagerInterface $entity_type_manager) {
  }
  /**
   * Returns a renderable array for a test page.
   *
   * return []
   */
  public function content() {
    $storage = $this->entity_type_manager->getStorage('node');
    $car_ids = $storage
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'cars')
      ->execute();
    $cars = $storage->loadMultiple($car_ids);

    $build = [
      '#theme' => 'vehicle_checker_content',
      '#cars' => $cars,
    ];
    return $build;
  }

  public function detail(NodeInterface $node) {
    // security check: node type "cars" only
    if ($node->bundle() !== 'cars') {
      throw new NotFoundHttpException();
    }

    // security check: release date 2020 not found
    $release_date = $node->get('field_release_date')->value;
    if ($release_date) {
      $year = date('Y', strtotime($release_date));

      // release date 2020: not found
      if ($year === '2020') {
        throw new NotFoundHttpException();
      }
    }

    return [
      '#theme' => 'vehicle_checker_detail',
      '#node' => $node,
      '#cache' => [
        'contexts' => ['route'],
        'tags' => $node->getCacheTags(),
      ],
    ];
  }

}
