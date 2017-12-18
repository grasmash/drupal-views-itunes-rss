Drupal 8 module for providing &lt;itunes:*> tags via Views RSS.

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
