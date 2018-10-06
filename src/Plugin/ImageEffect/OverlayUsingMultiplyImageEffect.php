<?php

namespace Drupal\image_effects_addon\Plugin\ImageEffect;

use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Image\ImageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\image\ConfigurableImageEffectBase;
use Drupal\image_effects\Component\ImageUtility;
use Drupal\image_effects\Plugin\ImageEffectsPluginBaseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\image_effects\Plugin\ImageEffect\AnchorTrait;

/**
 * Class OverlayUsingMultiplyImageEffect.
 *
 * @ImageEffect(
 *   id = "image_effects_addon_overlay_using_multiply",
 *   label = @Translation("Overlay using multiply"),
 *   description = @Translation("Overlay an image using the 'multiply' effect.")
 * )
 */
class OverlayUsingMultiplyImageEffect extends ConfigurableImageEffectBase implements ContainerFactoryPluginInterface {

  use AnchorTrait;

  /**
   * The image factory service.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * The image selector plugin.
   *
   * @var \Drupal\image_effects\Plugin\ImageEffectsPluginBaseInterface
   */
  protected $imageSelector;

  /**
   * Constructs an OverlayUsingMultiplyImageEffect object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The image factory service.
   * @param \Drupal\image_effects\Plugin\ImageEffectsPluginBaseInterface $image_selector
   *   The image selector plugin.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, LoggerInterface $logger, ImageFactory $image_factory, ImageEffectsPluginBaseInterface $image_selector) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger);
    $this->imageFactory = $image_factory;
    $this->imageSelector = $image_selector;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')->get('image'),
      $container->get('image.factory'),
      $container->get('plugin.manager.image_effects.image_selector')->getPlugin()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'overlay_image' => '',
      'overlay_width' => NULL,
      'overlay_height' => NULL,
      'placement' => 'center-center',
      'x_offset' => 0,
      'y_offset' => 0,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $summary = [
      '#theme' => 'image_effects_overlay_using_multiply_summary',
      '#data' => $this->configuration,
    ];
    $summary += parent::getSummary();

    // Get the human readable label for placement.
    $summary['#data']['placement'] = $this->anchorOptions()[$summary['#data']['placement']];

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $options = [
      '#title' => $this->t('Overlay image'),
      '#description' => $this->t('Image to use as the overlay.'),
      '#default_value' => $this->configuration['overlay_image'],
    ];
    $form['overlay_image'] = $this->imageSelector->selectionElement($options);
    $form['overlay_resize'] = [
      '#type' => 'details',
      '#title' => $this->t('Image resize'),
      '#description' => $this->t('Select dimensions either in pixels or as percentage of the <strong>current canvas</strong>. Leaving one dimension empty will resize the overlay image maintaining its aspect ratio. Leave both dimensions empty to apply the watermark in its original dimensions.'),
      '#open' => TRUE,
    ];
    $form['overlay_resize']['overlay_width'] = [
      '#type' => 'image_effects_px_perc',
      '#title' => $this->t('Image width'),
      '#default_value' => $this->configuration['overlay_width'],
      '#size' => 5,
      '#maxlength' => 5,
      '#required' => FALSE,
    ];
    $form['overlay_resize']['overlay_height'] = [
      '#type' => 'image_effects_px_perc',
      '#title' => $this->t('Image height'),
      '#default_value' => $this->configuration['overlay_height'],
      '#size' => 5,
      '#maxlength' => 5,
      '#required' => FALSE,
    ];
    $form['placement'] = [
      '#type' => 'radios',
      '#title' => $this->t('Placement'),
      '#options' => $this->anchorOptions(),
      '#theme' => 'image_anchor',
      '#default_value' => $this->configuration['placement'],
      '#description' => $this->t('Position of the image on the canvas.'),
    ];
    $form['x_offset'] = [
      '#type'  => 'image_effects_px_perc',
      '#title' => $this->t('Horizontal offset'),
      '#description'   => $this->t("Additional horizontal offset from placement. Enter a value, and specify if pixels or percent of the canvas width. '+' or no sign shifts the watermark rightward, '-' sign leftward."),
      '#default_value' => $this->configuration['x_offset'],
      '#maxlength' => 4,
      '#size' => 4,
    ];
    $form['y_offset'] = [
      '#type'  => 'image_effects_px_perc',
      '#title' => $this->t('Vertical offset'),
      '#description'   => $this->t("Additional vertical offset from placement. Enter a value, and specify if pixels or percent of the canvas height. '+' or no sign shifts the watermark downward, '-' sign upward."),
      '#default_value' => $this->configuration['y_offset'],
      '#maxlength' => 4,
      '#size' => 4,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['overlay_image'] = $form_state->getValue('overlay_image');
    $this->configuration['overlay_width'] = $form_state->getValue(['overlay_resize', 'overlay_width']);
    $this->configuration['overlay_height'] = $form_state->getValue(['overlay_resize', 'overlay_height']);
    $this->configuration['placement'] = $form_state->getValue('placement');
    $this->configuration['x_offset'] = $form_state->getValue('x_offset');
    $this->configuration['y_offset'] = $form_state->getValue('y_offset');
  }

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    // Get the watermark image object.
    $overlay_image = $this->imageFactory->get($this->configuration['overlay_image']);
    if (!$overlay_image->isValid()) {
      $this->logger->error('Image overlay failed using the %toolkit toolkit on %path', ['%toolkit' => $image->getToolkitId(), '%path' => $this->configuration['overlay_image']]);
      return FALSE;
    }

    // Determine watermark dimensions if they need to be changed.
    if ((bool) $this->configuration['overlay_width'] || (bool) $this->configuration['overlay_height']) {
      $overlay_aspect = $overlay_image->getHeight() / $overlay_image->getWidth();
      $overlay_width = ImageUtility::percentFilter($this->configuration['overlay_width'], $image->getWidth());
      $overlay_height = ImageUtility::percentFilter($this->configuration['overlay_height'], $image->getHeight());
      if ($overlay_width && !$overlay_height) {
        $overlay_height = (int) round($overlay_width * $overlay_aspect);
      }
      elseif (!$overlay_width && $overlay_height) {
        $overlay_width = (int) round($overlay_height / $overlay_aspect);
      }
    }
    else {
      $overlay_width = $overlay_image->getWidth();
      $overlay_height = $overlay_image->getHeight();
    }

    // Calculate position of watermark on source image based on placement
    // option.
    list($x, $y) = explode('-', $this->configuration['placement']);
    $x_pos = round(image_filter_keyword($x, $image->getWidth(), $overlay_width));
    $y_pos = round(image_filter_keyword($y, $image->getHeight(), $overlay_height));

    // Calculate offset based on px/percentage.
    $x_offset = (int) ImageUtility::percentFilter($this->configuration['x_offset'], $image->getWidth());
    $y_offset = (int) ImageUtility::percentFilter($this->configuration['y_offset'], $image->getHeight());

    return $image->apply('overlay_using_multiply', [
      'overlay_image' => $overlay_image,
      'overlay_width' => $overlay_width !== $overlay_image->getWidth() ? $overlay_width : NULL,
      'overlay_height' => $overlay_height !== $overlay_image->getHeight() ? $overlay_height : NULL,
      'x_offset' => $x_pos + $x_offset,
      'y_offset' => $y_pos + $y_offset,
    ]);
  }

}
