<?php

namespace Drupal\google_cloud_vision\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Google\Cloud\Vision\VisionClient;

/**
 * Plugin implementation of the 'google_cloud_vision_image_widget' widget.
 *
 * @FieldWidget(
 *   id = "google_cloud_vision_image_widget",
 *   label = @Translation("Google cloud vision image widget"),
 *   field_types = {
 *     "google_cloud_vision_image"
 *   }
 * )
 */
class GoogleCloudVisionImageWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return parent::settingsSummary();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['value'] = $element + [
      '#theme' => 'google_cloud_image_file_field',
      '#label' => $element['#title'],
      '#input_name' => Html::cleanCssIdentifier($element['#title'] . '-' . $delta),
      '#attached' => [
        'library' => ['google_cloud_vision/field'],
      ],
      '#element_validate' => ['\\' . __CLASS__ . '::validateFilestream'],
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    return $element;
  }

  /**
   * Validates a base64 representation of this image field.
   *
   * @param array $element
   *   A Google Cloud Vision Image Widget element.
   * @param FormStateInterface $form_state
   *   A form state of the form that saved this data.
   *
   * @return void
   */
  public static function validateFilestream(array $element, FormStateInterface $form_state) {
    $inputs = $form_state->getUserInput();
    foreach ($inputs as $key => $val) {
      if (strpos($key, 'bytestream') !== FALSE && $val !== '') {
        $result = self::googleCloudEvalImage($val);
        if ($result !== TRUE) {
          foreach ($result as $error) {
            $form_state->setError($element, 'We cannot accept the image because: ' . $error);
          }
        }
        break;
      }
    }
  }

  /**
   * Queries the google cloud vision api to validate an image from the field.
   *
   * @param string $byteStream
   *   A base64 string representing an image.
   *
   * @return array|boolean
   *   An array of errors from the google cloud vision api. TRUE if there are no errors.
   */
  private static function googleCloudEvalImage(string $byteStream) {
    $key_name = \Drupal::config('google_cloud_vision.settings')->get('key');
    $key = json_decode(\Drupal::service('key.repository')->getKey($key_name)->getKeyValue(), TRUE);
    $project_id = \Drupal::config('google_cloud_vision.settings')->get('project_id');
    try {
      $vision = new VisionClient([
        'projectId' => $project_id,
        'keyFile' => $key,
      ]);
    }
    catch (\Exception $e) {
      return [$e->getMessage()];
    }
    $image = $vision->image(base64_decode($byteStream), [
      'SAFE_SEARCH_DETECTION',
    ]);
    $annotation = $vision->annotate($image);
    $error = $annotation->error();
    if (!is_null($error)) {
      $result = [];
      $result[] = print_r($error, TRUE);
      return $result;
    }
    $safe_search = $annotation->safeSearch();
    $safe_search_props = [
      'isAdult' => t('Adult Content'),
      'isMedical' => t('Medical Content'),
      'isViolent' => t('Violent Content'),
      'isRacy' => t('Racy Content'),
    ];
    $results = [];
    foreach ($safe_search_props as $property => $description) {
      if ($safe_search->$property('high')) {
        $results[] = $description;
      }
    }
    if (!empty($results)) {
      return $results;
    }
    return TRUE;
  }
}
