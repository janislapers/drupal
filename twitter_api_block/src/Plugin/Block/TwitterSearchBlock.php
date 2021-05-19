<?php

namespace Drupal\twitter_api_block\Plugin\Block;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'TwitterSearchBlock' block.
 *
 * @Block(
 *   id = "twitter_search_block",
 *   admin_label = @Translation("Twitter - Search block"),
 *   category = @Translation("Content")
 * )
 */
class TwitterSearchBlock extends TwitterBlockBase {

  const DEFAULT_RESULT_TYPE = 'popular';

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    StateInterface $state,
    KillSwitch $kill_switch,
    LoggerChannelFactory $logger,
    LanguageManagerInterface $language_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $state, $kill_switch, $logger);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('state'),
      $container->get('page_cache_kill_switch'),
      $container->get('logger.factory'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form   = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $languages = [];
    foreach ($this->languageManager->getLanguages() as $langcode => $language) {
      $languages[$langcode] = $language->getName();
    }

    // Block options.
    $form['options']['search'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t("Search"),
      '#description'   => $this->t("Enter your query string (ex: #drupal or 'Drupal')."),
      '#default_value' => isset($config['options']['search']) ? $config['options']['search'] : NULL,
      '#required'      => TRUE,
    ];
    $form['options']['result_type'] = [
      '#type'          => 'select',
      '#options'       => [
        'mixed'   => $this->t('mixed'),
        'recent'  => $this->t('Recent'),
        'popular' => $this->t('Popular'),
      ],
      '#title'         => $this->t("Result type"),
      '#default_value' => isset($config['options']['result_type']) ? $config['options']['result_type'] : self::DEFAULT_RESULT_TYPE,
      '#required'      => TRUE,
    ];
    $form['options']['lang'] = [
      '#type'          => 'select',
      '#options'       => $languages,
      '#title'         => $this->t("Language"),
      '#default_value' => isset($config['options']['lang']) ? $config['options']['lang'] : $this->languageManager->getCurrentLanguage()->getId(),
    ];
    $form['options']['geocode'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t("Geocode"),
      '#default_value' => isset($config['options']['geocode']) ? $config['options']['geocode'] : NULL,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build  = parent::build();
    $config = $this->getConfiguration();

    if (!$this->hasCredentials()) {
      return [];
    }

    // Get latest tweets.
    $results = $this->getTweets($this->getUrl(), $this->getParameters());
    $tweets  = isset($results['statuses']) ? $results['statuses'] : [];

    // Return empty if no tweets found.
    if (!count($tweets)) {
      return [];
    }

    // Build renderable array of oembed tweets.
    $embed           = $this->renderTweets($tweets);
    $build['tweets'] = $this->displayTweets($embed);

    // Pass search to Twig.
    $build['search'] = [
      '#type'   => 'item',
      '#markup' => $config['options']['search'],
    ];

    return $build;
  }

  /**
   * {@inheritDoc}
   */
  private function getUrl() {
    return 'https://api.twitter.com/1.1/search/tweets.json';
  }

  /**
   * {@inheritDoc}
   */
  private function getParameters() {
    $config = $this->getConfiguration();

    return UrlHelper::buildQuery([
      'q'           => $config['options']['search'] ?? '',
      'count'       => $config['options']['count'] ?? parent::DEFAULT_COUNT,
      'result_type' => $config['options']['result_type'] ?? self::DEFAULT_RESULT_TYPE,
      'lang'        => $config['options']['lang'] ?? $this->languageManager->getCurrentLanguage()->getId(),
      'geocode'     => $config['options']['geocode'] ?? '',
    ]);
  }
}
