<?php

/**
* @file
* Contains Drupal\iframe_cookie_consent\Form\CookieconsentSettingsForm.
*/

namespace Drupal\iframe_cookie_consent\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class CookieconsentSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'iframe_cookie_consent.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cookieconsent_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('iframe_cookie_consent.settings');

    $form['cookieconsent_category'] = [
      '#title' => t('Cookieconsent category'),
      '#type' => 'select',
      '#description' => t(
        'To be able to enable the iframe when consent has been given,
        the data-cookieconsent attribute must be added with one of these values.'
      ),
      '#options' => [
        'preferences' => 'Preferences',
        'statistics' => 'Statistics',
        'marketing' => 'Marketing'
      ],
      '#default_value' => $config->get('cookieconsent_category')
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('iframe_cookie_consent.settings');
    $config->set('cookieconsent_category', $form_state->getValue('cookieconsent_category'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }
}
