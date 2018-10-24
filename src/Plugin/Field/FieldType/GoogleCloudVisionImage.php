<?php

namespace Drupal\google_cloud_vision\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\Plugin\Field\FieldType\StringItemBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'google_cloud_vision_image' field type.
 *
 * @FieldType(
 *   id = "google_cloud_vision_image",
 *   label = @Translation("Google cloud vision image"),
 *   description = @Translation("An image to be tested against the google cloud vision API."),
 *   default_widget = "google_cloud_vision_image_widget",
 *   default_formatter = "google_cloud_vision_formatter"
 * )
 */
class GoogleCloudVisionImage extends StringItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'blob',
          'size' => 'big',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    $values['value'] = $random->paragraphs();
    return $values;
  }
}
