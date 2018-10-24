<?php

namespace Drupal\google_cloud_vision\Controller;

use Drupal\Core\Controller\ControllerBase;
use Google\Cloud\Vision\VisionClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GoogleCloudVisionController.
 */
class GoogleCloudVisionController extends ControllerBase {

  /**
   * Demonstrates Google Vision API functionality.
   *
   * @return string
   *   Return Hello string.
   */
  public function demo() {
    $key_name = \Drupal::config('google_cloud_vision.settings')->get('key');
    $key = json_decode(\Drupal::service('key.repository')->getKey($key_name)->getKeyValue(), TRUE);
    $project_id = \Drupal::config('google_cloud_vision.settings')->get('project_id');

    // Create the Vision Client API.
    $vision = new VisionClient([
      // Name of the project.
      'projectId' => $project_id,

      // Authentication Key.
      // This is a JSON download from a Service Account
      // https://console.cloud.google.com/iam-admin/serviceaccounts
      'keyFile' => $key,
    ]);

    // Retrieve the image data.
    $image_path = "/" . drupal_get_path('module', 'google_cloud_vision') . "/img/einstein.jpg";
    $image_data = fopen(DRUPAL_ROOT . $image_path, 'r');

    // Tell Google Vision what we want to process.
    $image = $vision->image($image_data, [
      // @codingStandardsIgnoreStart
      'FACE_DETECTION',
      // 'LANDMARK_DETECTION',
      // 'LOGO_DETECTION',
      'LABEL_DETECTION',
      // 'TEXT_DETECTION',
      // 'DOCUMENT_TEXT_DETECTION',
      'SAFE_SEARCH_DETECTION',
      // 'IMAGE_PROPERTIES',
      // 'WEB_DETECTION',
      // @codingStandardsIgnoreEnd
    ]);

    // Process image with Google Vision.
    $annotation = $vision->annotate($image);

    // Process errors.
    $error = $annotation->error();
    if (!is_null($error)) {
      $this->messenger()->addError($error['message']);
      return ['#markup' => 'Error'];
    }

    // Parse face properties.
    $face_props = [
      'hasHeadwear' => $this->t('Headwear'),
      'isAngry' => $this->t('Anger'),
      'isBlurred' => $this->t('Blurred'),
      'isJoyful' => $this->t('Joy'),
      'isSorrowful' => $this->t('Sorrow'),
      'isSurprised' => $this->t('Surprise'),
      'isUnderExposed' => $this->t('Under Exposed'),
    ];
    $faces = $annotation->faces() ?? [];
    foreach ($faces as $face) {
      foreach ($face_props as $property => $description) {
        if ($face->$property()) {
          $face_results[] = (string) $description;
        }
      }
    }
    $image_variables = [
      '#theme' => 'image',
      '#uri' => $image_path,
      '#alt' => $this->t('Einstein'),
      '#title' => $this->t('Einstein'),
    ];
    $output = \Drupal::service('renderer')->render($image_variables);
    $output .= '<p>' . implode(', ', $face_results) . '</p>';
    return ['#markup' => $output];
  }

}
