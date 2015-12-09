<?php
/**
 * @file
 * Contains \Drupal\bootstrap\Plugin\Process\Actions.
 */

namespace Drupal\bootstrap\Plugin\Process;

use Drupal\bootstrap\Annotation\BootstrapProcess;
use Drupal\bootstrap\Plugin\PluginBase;
use Drupal\bootstrap\Utility\Element;
use Drupal\Core\Form\FormStateInterface;

/**
 * Processes the "actions" element.
 *
 * @BootstrapProcess(
 *   id = "actions"
 * )
 */
class Actions extends PluginBase implements ProcessInterface {

  /**
   * {@inheritdoc}
   */
  public static function process(array $element, FormStateInterface $form_state, array &$complete_form) {
    if (!empty($element['#bootstrap_ignore_process'])) {
      return $element;
    }

    foreach (Element::create($element)->children() as $child) {
      $child->setIcon();
    }

    return $element;
  }

}
