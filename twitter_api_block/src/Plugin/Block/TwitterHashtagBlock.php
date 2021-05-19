<?php

namespace Drupal\twitter_api_block\Plugin\Block;

use Drupal\Core\Cache\UncacheableDependencyTrait;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'TwitterHashtagBlock' block.
 *
 * @Block(
 *   id = "twitter_hashtag_block",
 *   admin_label = @Translation("Twitter - Hashtag block (DO NOT USE - deprecated)"),
 *   category = @Translation("Content")
 * )
 */
class TwitterHashtagBlock extends TwitterBlockBase {

  use UncacheableDependencyTrait;

  const ERROR_MESSAGE = "This block is deprecated. Please use the 'Twitter - Search block' instead.";

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['options']['warning'] = [
      '#type'   => 'item',
      '#markup' => $this->t(self::ERROR_MESSAGE),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $form_state->setErrorByName('warning', $this->t(self::ERROR_MESSAGE));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [];
  }

}
