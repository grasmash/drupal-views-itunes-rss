Drupal 8 module for providing &lt;itunes:*> tags via Views RSS.

This module provides a new Views formatter for rendering Views RSS feeds as iTunes podcast feeds. It essentially allows you to map fields on Drupal entities to iTunes elements. E.g., you may map a node's `field_episode_recording` file field to the `<enclosure>` XML element, or a node's `field_explicit` boolean field to the `<itunes:explicit>` XML element.

# Set up

1. Create a new view with a 'feed' display.
1. Add the entity fields that will be rendered as iTunes elements. E.g., add the file field that should be rendered as the `<enclosure>`.
1. Select "iTunes RSS Feed" as the display __format__.
1. Set <channel> settings under settings for "iTunes RSS Feed".
1. Select "iTunes Fields" under format's __show__ setting.
1. Set the <item> settings under settings for "iTunes Fields".

# Customize

You may customize the output using Drupal's core theming system. The following twig templates may be overridden:

* views-view-itunes-rss.html.twig
* views-view-row-rss.html.twig

The following preprocessor may be overridden: `template_preprocess_views_view_itunes_rss()`.
