<?php

namespace Drupal\embassies_main_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\domain\DomainNegotiatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Custom node redirect controller.
 */
class NodesController extends ControllerBase {

  /**
   * The domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * EntityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * FormBuilder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $entityForm;

  /**
   * Constructs NodesController object.
   */
  public function __construct(
    DomainNegotiatorInterface $domain_negotiator,
    EntityTypeManagerInterface $entity_type_manager,
    FormBuilderInterface $entity_form
  ) {
    $this->domainNegotiator = $domain_negotiator;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityForm = $entity_form;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): NodesController|static {
    return new static(
      $container->get('domain.negotiator'),
      $container->get('entity_type.manager'),
      $container->get('form_builder'),
    );
  }

  /**
   * Departments node edit redirect controller.
   *
   * Shows node edit form at /the-embassy/departments.
   */
  public function departmentsNode(): array {
    return $this->currentForm('departments');
  }

  /**
   * Ambassador/Consul node edit redirect controller.
   *
   * Shows node edit form at /the-embassy/the-ambassador.
   */
  public function ambassadorNode(): array {
    return $this->currentForm('ambassador_consul');
  }

  /**
   * About the embassy node edit redirect controller.
   *
   * Shows node edit form at /the-embassy/about.
   */
  public function aboutNode(): array {
    return $this->currentForm('about_the_embassy');
  }

  /**
   * Bilateral relations node edit redirect controller.
   *
   * Shows node edit form at /the-embassy/bilateral-relations.
   */
  public function bilateralNode(): array {
    return $this->currentForm('bilateral_relations');
  }

  /**
   * About pages controller.
   *
   * Shows node edit forms at /admin/$type.
   */
  public function currentForm($type): array {
    // Get the current domain.
    if (!empty($active_domain = $this->domainNegotiator->getActiveDomain())) {
      $domain_id = $active_domain->id();

      // Check if there are any instances of node entity type.
      $node_storage = $this->entityTypeManager->getStorage('node');

      // Get the $type nodes of the domain.
      $nid = $node_storage->getQuery()
        ->condition('type', $type)
        ->condition('field_domain_access', $domain_id)
        ->execute();

      // Get the latest one if there are few of them.
      if (!empty($nid)) {
        $latest_node = $node_storage->load(end($nid));
        $form = $this->entityTypeManager
          ->getFormObject('node', 'default')
          ->setEntity($latest_node);
        return $this->entityForm->getForm($form);
      }
    }
    throw new AccessDeniedHttpException();
  }

}
