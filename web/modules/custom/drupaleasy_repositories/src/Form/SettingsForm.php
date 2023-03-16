<?php

namespace Drupal\drupaleasy_repositories\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure DrupalEasy Repositories settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The DrupalEasy repositories manager service.
   *
   * @var \Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesPluginManager
   */
  protected DrupaleasyRepositoriesPluginManager $repositoriesManager;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'drupaleasy_repositories_settings';
  }

  /**
   * Constructs an SettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesPluginManager $drupaleasy_repositories_manager
   *   The DrupalEasy repositories manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, DrupaleasyRepositoriesPluginManager $drupaleasy_repositories_manager) {
    parent::__construct($config_factory);
    $this->repositoriesManager = $drupaleasy_repositories_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.drupaleasy_repositories')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @return array<int, string>
   *   List of editable configurations.
   */
  protected function getEditableConfigNames(): array {
    return ['drupaleasy_repositories.settings'];
  }

  /**
   * {@inheritdoc}
   *
   * @param array<string, mixed> $form
   *   The form array.
   *
   * @return array<int, mixed>
   *   The form array.
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $repositories = $this->repositoriesManager->getDefinitions();
    $repositories_config = $this->config('drupaleasy_repositories.settings');

    uasort($repositories, function ($a, $b) {
      return Unicode::strcasecmp($a['label'], $b['label']);
    });
    $repository_options = [];
    foreach ($repositories as $repository => $definition) {
      $repository_options[$repository] = $definition['label'];
    }

    $form['repositories'] = [
      '#type' => 'checkboxes',
      '#options' => $repository_options,

      '#title' => $this->t('Repositories'),
      '#default_value' => $repositories_config->get('repositories'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
//  public function validateForm(array &$form, FormStateInterface $form_state) {
//    if ($form_state->getValue('example') != 'example') {
//      $form_state->setErrorByName('example', $this->t('The value is not correct.'));
//    }
//    parent::validateForm($form, $form_state);
//  }

  /**
   * {@inheritdoc}
   *
   * @param array<string, mixed> $form
   *   The form array.
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('drupaleasy_repositories.settings')
      ->set('repositories', $form_state->getValue('repositories'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
