<?php

namespace Drupal\iframe_cookie_consent\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Masterminds\HTML5;

/**
 * Provides a filter to help celebrate good times!
 *
 * @Filter(
 *   id = "iframecookieconsent",
 *   title = @Translation("Iframe cookie consent"),
 *   description = @Translation("Hide and show content based on the visitor's consent"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 * )
 */
class IframeCookieConsent extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    // Get cookie consent settings.
    $config = \Drupal::config('iframe_cookie_consent.settings');
    // Get cookie consent category.
    $consent_cat = $config->get('cookieconsent_category') ?? 'marketing';
    // Load Ckeditor content to DOMDocument.
    $html5 = new HTML5();
    libxml_use_internal_errors(TRUE);
    $dom = $html5->loadHTML($text);
    // Spotify, Youtube & Vimeo regex pattern.
    $regex_pattern = "/(open.spotify.com|youtube.com|youtu.be|player.vimeo.com)\/(embed)?(video)?(\?v=)?(\S+)?/";
    $match = NULL;

    // Loop all content iframes.
    foreach ($dom->getElementsByTagName('iframe') as $iframe) {
      // Get iframe url.
      $url = $iframe->getAttribute('src');

      // Check if iframe has Youtube url.
      if (preg_match($regex_pattern, $url, $match)) {
        $match = TRUE;
        // Change src attribute to data-cookieblock-src.
        $iframe->setAttribute('data-cookieblock-src', $iframe->getAttribute('src'));
        $iframe->removeAttribute('src');
        // Set cookieconsent category value.
        $iframe->setAttribute('data-cookieconsent', $consent_cat);
        // Visible when user has opted in, otherwise hidden.
        // Create div element with right class and value.
        $optin = $dom->createElement('div', $this->t('This content is only visible when the visitor has consented to marketing cookies.'));
        $optin->setAttribute('class', 'cookieconsent-optin-' . $consent_cat);
        // Visible when user has not yet submitted a consent or
        // has opted out of all but strictly necessary cookies,
        // otherwise hidden.
        // First create div with right class.
        $optout = $dom->createElement('div');
        $optout->setAttribute('class', 'cookieconsent-optout-' . $consent_cat);
        // Use a helper to create string including html.
        $helper = new \DOMDocument();
        $helper->loadHTML($this->t('Please <a href="javascript:Cookiebot.renew()">accept marketing-cookies</a> to watch this content.'));
        // Put optout html string inside optout div.
        $optout->appendChild($dom->importNode($helper->documentElement, TRUE));
        // Insert optin element before iframe.
        $iframe->parentNode->insertBefore($optin, $iframe);
        // Insert optout element before iframe.
        $iframe->parentNode->insertBefore($optout, $iframe);
      }
    }

    if ($match) {
      $text = $dom->saveHTML($dom->documentElement);
    }

    return new FilterProcessResult($text);
  }
}
