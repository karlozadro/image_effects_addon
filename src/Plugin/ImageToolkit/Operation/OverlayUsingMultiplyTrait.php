<?php

namespace Drupal\image_effects_addon\Plugin\ImageToolkit\Operation;

use Drupal\Core\Image\ImageInterface;

/**
 * Base trait for image_effects Overlay using multiply operations.
 */
trait OverlayUsingMultiplyTrait {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'overlay_image' => [
        'description' => 'Overlay image.',
      ],
      'overlay_width' => [
        'description' => 'Width of overlay image.',
        'required' => FALSE,
        'default' => NULL,
      ],
      'overlay_height' => [
        'description' => 'Height of overlay image.',
        'required' => FALSE,
        'default' => NULL,
      ],
      'x_offset' => [
        'description' => 'X offset for overlay image.',
        'required' => FALSE,
        'default' => 0,
      ],
      'y_offset' => [
        'description' => 'Y offset for overlay image.',
        'required' => FALSE,
        'default' => 0,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function validateArguments(array $arguments) {
    // Ensure watermark_image is an expected ImageInterface object.
    if (!$arguments['overlay_image'] instanceof ImageInterface) {
      throw new \InvalidArgumentException("Overlay image passed to the 'overlay using multiply' operation is invalid");
    }
    // Ensure watermark_image is a valid image.
    if (!$arguments['overlay_image']->isValid()) {
      $source = $arguments['overlay_image']->getSource();
      throw new \InvalidArgumentException("Invalid image at {$source}");
    }
    // Ensure 'watermark_width' is NULL or a positive integer.
    $arguments['overlay_width'] = $arguments['overlay_width'] !== NULL ? (int) $arguments['overlay_width'] : NULL;
    if ($arguments['overlay_width'] !== NULL && $arguments['overlay_width'] <= 0) {
      throw new \InvalidArgumentException("Invalid overlay width ('{$arguments['overlay_width']}') specified for the image 'overlay using multiply' operation");
    }
    // Ensure 'watermark_height' is NULL or a positive integer.
    $arguments['overlay_height'] = $arguments['overlay_height'] !== NULL ? (int) $arguments['overlay_height'] : NULL;
    if ($arguments['overlay_height'] !== NULL && $arguments['overlay_height'] <= 0) {
      throw new \InvalidArgumentException("Invalid height ('{$arguments['overlay_height']}') specified for the image 'overlay using multiply' operation");
    }
    // Ensure 'x_offset' is an integer.
    $arguments['x_offset'] = (int) $arguments['x_offset'];
    // Ensure 'y_offset' is an integer.
    $arguments['y_offset'] = (int) $arguments['y_offset'];
    return $arguments;
  }

}
