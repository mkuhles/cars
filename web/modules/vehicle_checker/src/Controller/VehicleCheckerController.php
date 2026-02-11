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
    if ($node->bundle() !== 'cars') {
      throw new NotFoundHttpException();
    }

    if (!$node->access('view')) {
      throw new NotFoundHttpException();
    }
    $release_year = NULL;
    if ($node->hasField('field_release_date') && !$node->get('field_release_date')->isEmpty()) {
      $release_year = $node->get('field_release_date')->first()?->date?->format('Y');
    }
    if($release_year == 2020) {
      throw new NotFoundHttpException($this->t('This vehicle was released in 2020!'));
    }
  
    $build = [
      '#theme' => 'vehicle_checker_detail',
      '#node' => $node,
      '#release_year' => $release_year,
    ];
    return $build;
  }

}
