<?php
/**
 * @file
 * Contains \Drupal\bootstrap\Plugin\ProcessManager.
 */

namespace Drupal\bootstrap\Plugin;

use Drupal\bootstrap\Bootstrap;
use Drupal\bootstrap\Theme;
use Drupal\bootstrap\Utility\Element;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;

/**
 * Manages discovery and instantiation of Bootstrap form process callbacks.
 */
class ProcessManager extends PluginManager {

  /**
   * Constructs a new \Drupal\bootstrap\Plugin\ProcessManager object.
   *
   * @param \Drupal\bootstrap\Theme $theme
   *   The theme to use for discovery.
   */
  public function __construct(Theme $theme) {
    parent::__construct($theme, 'Plugin/Process', 'Drupal\bootstrap\Plugin\Process\ProcessInterface', 'Drupal\bootstrap\Annotation\BootstrapProcess');
    $this->setCacheBackend(\Drupal::cache('discovery'), 'theme:' . $theme->getName() . ':process', $this->getCacheTags());
  }

  /**
   * Global #process callback for form elements.
   *
   * @param array $element
   *   The element render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The altered element array.
   *
   * @see \Drupal\bootstrap\Plugin\Alter\ElementInfo::alter
   */
  public static function process(array $element, FormStateInterface $form_state, array &$complete_form) {
    if (!empty($element['#bootstrap_ignore_process'])) {
      return $element;
    }

    static $theme;
    if (!isset($theme)) {
      $theme = Bootstrap::getTheme();
    }

    $e = new Element($element, $form_state);

    // Add necessary classes.
    $e->replaceClass('container-inline', 'form-inline');
    if ($e->hasClass('form-wrapper')) {
      $e->addClass('form-group');
    }

    // Check for errors and set the "has_error" property flag.
    if ($e->hasError() || ($e->getProperty('required') && $theme->getSetting('forms_required_has_error'))) {
      $e->setProperty('has_error', TRUE);
    }

    // Automatically inject the nearest button found after this element if
    // #input_group_button exists.
    if ($e->getProperty('input_group_button')) {
      // Obtain the parent array to limit search.
      $array_parents = $e->getProperty('array_parents', []);

      // Remove the current element from the array.
      array_pop($array_parents);

      // If element is nested, return the referenced parent from the form.
      // Otherwise return the complete form.
      $parent = new Element($array_parents ? NestedArray::getValue($form, $array_parents) : $complete_form);

      // Ignore buttons before we find the element in the form.
      $current = FALSE;
      foreach ($parent->children() as $child) {
        if ($child->getArray() === $element) {
          $current = $child;
          continue;
        }

        if ($current && $child->isButton()) {
          $child->setIcon();
          $e->setProperty('field_suffix', $child->getArray());
          break;
        }
      }
    }

    return $element;
  }

}
