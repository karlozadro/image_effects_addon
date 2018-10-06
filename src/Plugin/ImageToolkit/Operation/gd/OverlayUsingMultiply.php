<?php

namespace Drupal\image_effects_addon\Plugin\ImageToolkit\Operation\gd;

use Drupal\image_effects_addon\Plugin\ImageToolkit\Operation\OverlayUsingMultiplyTrait;
use Drupal\system\Plugin\ImageToolkit\Operation\gd\GDImageToolkitOperationBase;
use Drupal\image_effects\Plugin\ImageToolkit\Operation\gd\GDOperationTrait;

/**
 * Defines GD2 set_gif_transparent_color image operation.
 *
 * @ImageToolkitOperation(
 *   id = "image_effects_addon_gd_overlay_using_multiply",
 *   toolkit = "gd",
 *   operation = "overlay_using_multiply",
 *   label = @Translation("Overlay using multiply"),
 *   description = @Translation("Overlay an image using the multiply effect.")
 * )
 */
class OverlayUsingMultiply extends GDImageToolkitOperationBase {

    use GDOperationTrait;
    use OverlayUsingMultiplyTrait;

    /**
     * {@inheritdoc}
     */
    protected function execute(array $arguments) {
        $overlay = $arguments['overlay_image'];

        // Resize watermark if needed.
        if ($arguments['overlay_width'] || $arguments['overlay_height']) {
            $overlay->apply('resize', ['width' => $arguments['overlay_width'], 'height' => $arguments['overlay_height']]);
        }

        for ($x = 0; $x < $overlay->getWidth(); $x++) {
            for ($y = 0; $y < $overlay->getHeight(); $y++) {
                // Deal with images with mismatched sizes
                if ($x < $overlay->getWidth() && $y < $overlay->getHeight()) {
                    $colorBase = imagecolorsforindex($this->getToolkit()->getResource(), imagecolorat($this->getToolkit()->getResource(), $x, $y));
                    $colorOverlay = imagecolorsforindex($overlay->getToolkit()->getResource(), imagecolorat($overlay->getToolkit()->getResource(), $x, $y));

                    $newR = round($colorBase['red'] * $colorOverlay['red'] / 255);
                    $newG = round($colorBase['green'] * $colorOverlay['green'] / 255);
                    $newB = round($colorBase['blue'] * $colorOverlay['blue'] / 255);

                    $newColor = imagecolorallocate($this->getToolkit()->getResource(), $newR, $newG, $newB);
                    imagesetpixel($this->getToolkit()->getResource(), $x, $y, $newColor);
                }
            }
        }
        return TRUE;
    }

}
