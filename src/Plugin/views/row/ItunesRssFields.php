<?php

namespace Drupal\itunes_rss\Plugin\views\row;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\file\Plugin\Field\FieldType\FileFieldItemList;
use Drupal\views\Plugin\views\row\RssFields;

/**
 * Renders an iTunes RSS item based on fields.
 *
 * @ViewsRow(
 *   id = "itunes_rss_fields",
 *   title = @Translation("iTunes Fields"),
 *   help = @Translation("Display fields as iTunes RSS items."),
 *   theme = "views_view_row_rss",
 *   display_types = {"feed"}
 * )
 */
class ItunesRssFields extends RssFields {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['enclosure_field'] = ['default' => ''];
    foreach ($this->getItunesItemFields() as $field) {
      $options['itunes']['contains'][$this->getItunesFieldMachineName($field)] = ['default' => ''];
    }

    return $options;
  }

  /**
   * Get a list of all itunes:* fields that apply to the <item> element.
   *
   * @return array
   *   A flat array of field names.
   *
   * @see https://help.apple.com/itc/podcasts_connect/#/itcb54353390
   */
  public function getItunesItemFields() {
    $fields = [
      'subtitle',
      'summary',
      'title',
      'episodeType',
      'episode',
      'season',
      'author',
      'summary',
      'duration',
      'explicit',
      'block',
      'duration',
      'image',
      'isClosedCaptioned',
      'order',
    ];

    return $fields;
  }

  /**
   * Returns a list of fields to be rendered as boolean.
   *
   * @return array
   *   An array of fields to be rendered as boolean.
   */
  public function getItunesItemBooleanFields() {
    return [
      'explicit',
      'block',
      'isClosedCaptioned',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getItunesFieldMachineName($field) {
    return $field . "_field";
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $initial_labels = ['' => $this->t('- None -')];
    $view_fields_labels = $this->displayHandler->getFieldLabels();
    $view_fields_labels = array_merge($initial_labels, $view_fields_labels);

    $form['enclosure_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Enclosure field'),
      '#description' => $this->t('Describes a media object that is attached to the item. This must be a file field.'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['enclosure_field'],
    ];

    $form['itunes'] = [
      '#type' => 'details',
      '#title' => $this->t('iTunes fields'),
      '#open' => TRUE,
    ];
    $form['itunes']['help']['#markup'] = $this->t(
      'See @link for detailed information on available iTunes tags.',
      ['@link' => 'https://help.apple.com/itc/podcasts_connect/#/itcb54353390']
    );
    foreach ($this->getItunesItemFields() as $field) {
      $form['itunes'][$this->getItunesFieldMachineName($field)] = [
        '#type' => 'select',
        '#title' => $this->t('iTunes @field_name field', ['@field_name' => $field]),
        '#description' => $this->t("The itunes:@field_name field. If set to none, field will not be rendered.", ['@field_name' => $field]),
        '#options' => $view_fields_labels,
        '#default_value' => $this->options['itunes'][$this->getItunesFieldMachineName($field)],
        '#required' => FALSE,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function render($row) {
    $build = parent::render($row);
    static $row_index;
    if (!isset($row_index)) {
      $row_index = 0;
    }
    $item = $build['#row'];

    if ($this->options['enclosure_field']) {
      $field_name = $this->options['enclosure_field'];
      $entity = $this->view->result[$row_index]->_entity;
      $enclosure = $entity->$field_name;
      if ($enclosure instanceof FileFieldItemList) {
        $value = $enclosure->getValue();
        $file = File::load($value[0]['target_id']);
        $item->elements[] = [
          'key' => 'enclosure',
          'attributes' => [
            // In RSS feeds, it is necessary to use absolute URLs. The 'url.site'
            // cache context is already associated with RSS feed responses, so it
            // does not need to be specified here.
            'url' => file_create_url($file->getFileUri()),
            'length' => $file->getSize(),
            'type' => $file->getMimeType(),
          ]
        ];
      }
    }

    $fields = $this->getItunesItemFields();

    // Render boolean fields as yes/no.
    foreach ($this->getItunesItemBooleanFields() as $boolean_field) {
      if ($this->options['itunes'][$this->getItunesFieldMachineName($boolean_field)]) {
        $explicit = $this->getField($row_index,
          $this->options['itunes'][$this->getItunesFieldMachineName($boolean_field)]);
        $item->elements[] = [
          'key' => 'itunes:' . $boolean_field,
          'value' => $explicit ? "yes" : "no",
        ];
        // Unset so that field is not rendered again later.
        unset($fields[$boolean_field]);
      }
    }

    // Render remaining fields.
    foreach ($fields as $field) {
      if ($this->getField($row_index, $this->options['itunes'][$this->getItunesFieldMachineName($field)]) !== '') {
        $value = $this->getField($row_index, $this->options['itunes'][$this->getItunesFieldMachineName($field)]);
        $item->elements[] = [
          'key' => 'itunes:' . $field,
          'value' => $value,
        ];
      }
    }

    return $build;
  }
}
