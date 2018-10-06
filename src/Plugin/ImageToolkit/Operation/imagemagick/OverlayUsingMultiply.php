<?php

namespace Drupal\image_effects_addon\Plugin\ImageToolkit\Operation\imagemagick;

use Drupal\image_effects\Plugin\ImageToolkit\Operation\OverlayUsingMultiplyTrait;
use Drupal\imagemagick\Plugin\ImageToolkit\Operation\imagemagick\ImagemagickImageToolkitOperationBase;
use Drupal\image_effects\Plugin\ImageToolkit\Operation\imagemagick\ImagemagickOperationTrait;

/**
 * Defines ImageMagick overlay_using_multiply operation.
 *
 * @ImageToolkitOperation(
 *   id = "image_effects_addon_imagemagick_overlay_using_multiply",
 *   toolkit = "imagemagick",
 *   operation = "overlay_using_multiply",
 *   label = @Translation("Overlay using multiply"),
 *   description = @Translation("Overlay an image using the multiply effect.")
 * )
 */
class OverlayUsingMultiply extends ImagemagickImageToolkitOperationBase {

  use ImagemagickOperationTrait;
  use OverlayUsingMultiplyTrait;

  /**
   * {@inheritdoc}
   */
  protected function execute(array $arguments) {
    // No-op for now since we're just focused on GD
    return TRUE;
  }

}
