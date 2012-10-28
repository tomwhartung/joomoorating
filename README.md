joomoorating
============

JoomooRating: joomla component and plugin that allows rating of content articles, gallery groups and images


 JoomooRating
==============
This extension consists of a component and two plugins that allow users to
rate content articles and gallery images on a scale of one to ten.

The rating appears as a series of ten bars, similar to the bars on a cell
phone that show the strength of the signal.

 Features
----------
The JoomooRating extension contains PHP and Javascript code that:

o  Provides users the ability to rate an article or joomoogallery image.
o  Has backend functionality giving site administrators complete control over:
   o  Which articles and images can be rated
   o  How the rating bars appear: color, background, and hover bump
   o  What the rating rates: Agreeability, Likability, Believability, etc.
   o  Where the rating appears
   o  What the various levels mean
o  A fixed rating plugin, for cases when the rating is a "done deal"
   o  This is useful, for example, when the rating is irrelevant due to the
      nature of the article or image, or the type of rating
   o  Also useful in cases when the site administrator wants to close voting
      for some reason but wants to preserve the average rating

 Database Columns
------------------
Following are the columns in the jos_joomoorating table:

Field            Type               Description
-----------------------------------------------------------------------
id               int(11) unsigned   Standard joomla primary key
contentid        int(11)            Foreign key: jos_content
galleryimageid   int(11)            Foreign key: jos_joomoogallery
vote_count       int(11)            Number of votes
vote_total       int(11)            Total rating value of all votes
timestamp        timestamp          Date and time stamp

 JoomoooRating Plugin: Basic Backend Parameters
------------------------------------------------
all_articles
    Show rating for all articles or use placeholder for specific articles?
    Options: All articles or Use placeholder
rating_label
    Description of rating
    Text field
rating_bar_color
    Color of rating bars
    Options: Black, Blue, Green, Red, Yellow, or White
rating_bar_background
    Color - or lack thereof - used for background of rating bars
    Options: Black, Transparent, or White
where_in_article
    Where to show article rating - used only when article has no placeholder
    Options: Above article or Below article 
where_on_gallery_page
    Where to show gallery image rating - used when description has no placeholder
    Options: Above image, Below image, or Below description
hover_width_bump
    Number of pixels to add to bar width when user hovers mouse over it
    Options: Range from 1 to 15 pixels
ajax_or_full:
    Use ajax or full request to save and delete comments?
    Options: Use Ajax request only, Use Full request only, or Allow Either
allow_anonymous
    Allow users who aren't logged in to vote on articles and gallery images?
    Options: OK or Disallow
max_consecutive_ratings
    Max times user can vote on a article or gallery image between page loads
    Options: Range from 1 to 20, or Unlimited

 JoomoooRating Plugin: Advanced Backend Parameters
---------------------------------------------------
rating_description_1
    Description of rating value 1
    Text Field
rating_description_2
    Description of rating value 2
    Text Field
rating_description_3
    Description of rating value 3
    Text Field
rating_description_4
    Description of rating value 4
    Text Field
rating_description_5
    Description of rating value 5
    Text Field
rating_description_6
    Description of rating value 6
    Text Field
rating_description_7
    Description of rating value 7
    Text Field
rating_description_8
    Description of rating value 8
    Text Field
rating_description_9
    Description of rating value 9
    Text Field
rating_description_10
    Description of rating value 10
    Text Field

 JoomoooFixedRating Plugin: Basic Backend Parameters
-----------------------------------------------------
rating_label
    Description of rating.
    Text Field
rating_subtitle
    Default text to appear beneath rating bars.  Overridable in placeholder.
    Text Field
rating_value
    Default rating value for all articles and gallery images using this plugin.
        Overridable in placeholder.
    Options: Range from 1 to 10
rating_bar_color
    Color of rating bars.  Idea: choose black or white because these are inactive
    Options: Black, Blue, Green, Red, Yellow, or White
rating_bar_background
    Color - or lack thereof - used for background of rating bars
    Options: Black, Transparent, or White

