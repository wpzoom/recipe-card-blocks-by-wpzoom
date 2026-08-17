=== Recipe Card Blocks Lite ===
Contributors: WPZOOM
Donate link: https://recipecard.io/
Tags: recipe, recipe card, recipes, recipe maker, schema
Requires at least: 6.5
Requires PHP: 7.4
Tested up to: 7.1
Stable tag: 3.5.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Recipe Card Blocks with Schema Markup — create SEO-optimized recipes with Gutenberg, Elementor & AMP support

== Description ==

= The Ultimate WordPress Recipe Plugin for your Food Blog =

Trusted by thousands of food bloggers to rank higher in Google with structured recipe data.

**[Recipe Card Blocks](https://recipecard.io)** is a powerful WordPress recipe plugin that adds beautiful recipe cards to the **block editor & Elementor** to help you create SEO-optimized recipes on your food blog.

[youtube https://www.youtube.com/watch?v=TehuLXQXNi8]

🥑 **[View Demo](https://demo.recipecard.io/)** 🥑

⭐️ **[Recipe Card Blocks PRO](https://recipecard.io)** ⭐️

📩 [Subscribe to our newsletter](https://recipecard.io/newsletter/) for updates

> Did you find this plugin helpful? Please consider [leaving a 5-star review](https://wordpress.org/support/plugin/recipe-card-blocks-by-wpzoom/reviews/).

= 📌 WHY RECIPE CARD BLOCKS? =

* **Schema.org markup that Google reads** — get rich snippets with recipe name, image, ratings, and cook time directly in search results
* **Works with Gutenberg, Elementor & AMP** — no lock-in to a single page builder
* **AI Recipe Generator included for free** — [generate complete recipes with AI](https://recipecard.io/ai-recipe-generator/)
* **Import from WP Recipe Maker** — switch in one click without losing your recipes

= 📌 INCLUDED BLOCKS =

* **Recipe Card (with Schema.org Markup)**
* **Recipe Details**
* **Ingredients**
* **Directions**
* **Nutrition Facts**
* **Recipe Card Widget for Elementor**

= 📌 FREE FEATURES =

* **AI Recipe Generator** 🆕
* **Elementor Support** with dedicated recipe widget
* **Schema.org Structured Data** (JSON-LD)
* **3 Recipe Card Styles**
* **Inline Structured Data Validator**
* **AMP Support**
* Bulk Add Ingredients and Directions
* Video integration
* Import recipes from WP Recipe Maker
* WPML Support
* Recipe Shortcode
* Works with any theme
* GDPR-compliant


= ⭐️ PRO FEATURES ⭐️ =

**Boost your SEO & Traffic:**

* **Star Rating** — display star ratings in Google search results
* **Recipe Index Block** — searchable recipe catalog that keeps visitors on your site
* **Recipe Roundups** 🆕 — curate themed recipe collections that rank for long-tail keywords

**Engage your readers:**

* **Adjustable Servings** — readers scale ingredient quantities in real-time
* **Unit Conversion (US ↔ Metric)** 🆕 — switch between measurement systems with one click
* **Cook Mode** — keeps the screen awake while cooking
* **Comments Rating** — readers rate recipes directly in comments

**Grow your blog:**

* **Recipe Submissions** 🆕 — accept user-submitted recipes
* **WooCommerce Integration** 🆕
* **Grow.me Save Recipe Button** 🆕
* **Social Call-to-action** (Facebook, Instagram, Pinterest)
* **Advanced Pinterest Settings** — custom Pin image & description

**Professional design:**

* **5 Recipe Card Styles** (vs 3 in free)
* **4 Color Schemes + Unlimited Custom Colors**
* **Equipment Block** — showcase tools needed for each recipe
* **Image Gallery & Lightbox in Directions**
* **Food Labels** 🆕
* **Print Preview** with customizable credit text
* **Premium Support**

⭐️ **[Get the PRO version!](https://recipecard.io)** ⭐️



= 📌 Where I can view a Demo? =

You can view the Recipe Card Block live [here](http://demo.recipecard.io/).

= 🙌 FOLLOW US =

* 🐦 [Twitter](https://twitter.com/recipeblock)
* 📘 [Facebook](https://facebook.com/recipeblock)
* 📘 [Facebook Group for Food Bloggers](https://www.facebook.com/groups/recipeblock)
* 🌄 [Instagram](https://instagram.com/recipecardblocks)

= 100% GDPR COMPLIANT =

This plugin is **100% GDPR compliant**. It doesn't integrate any Google Fonts.
Recipe Card Blocks does not collect any information outside your WordPress installation, therefore it’s **100% GDPR compliant**.


== Installation ==

Simply search for the plugin name "Recipe Card Blocks" via the **Plugins -> Add New** page in the Dashboard of your WordPress website and click the install button. Once installed, click on the blue Activate button.

After installation, create a new post or edit an existing one using the block editor (Gutenberg), and when adding a new block, look under  **Recipe Card Blocks** section to find the "Recipe Card" block.


== Frequently Asked Questions ==

= How do I get star ratings to show in Google search results? =

Star ratings are enabled by default. Once at least one visitor rates a recipe, the plugin adds an `aggregateRating` to the recipe's structured data, which is what Google needs before it can show stars. Google decides if and when to display them, and it can take a while for your pages to be re-crawled. You can check that the markup is correct with Google's Rich Results Test.

= How do I turn ratings off? =

Go to Recipe Cards > Settings > Ratings. "User Rating" controls the stars on the recipe card, and "Comment Ratings" controls the star field in the comment form. Both can be switched off independently.

= What data do ratings store? =

Each rating stores the rating value, the recipe ID, the date and the visitor's IP address, plus the user ID for logged-in visitors. The IP address is used to stop the same visitor rating a recipe twice and to rate-limit submissions. If a visitor submits a written review, the name, email and review text are stored too. All of it stays in your own database and is never sent anywhere. See the suggested text under Tools > Privacy for wording you can use in your privacy policy.

= Where do I moderate ratings? =

Recipe Cards > Ratings lists every rating with approve, unapprove and delete actions. Ratings left through the comment form follow the comment's own approval status.

= I just installed the plugin and can't find the blocks =

Make sure you haven't disabled the new block editor using the Classic Editor plugin, as these blocks work only with the new editor.

= Is there Documentation available? =

Yes, you can find documentation for this plugin with more instructions on our website.

[Go to Documentation](https://recipecard.io/documentation/)

= Is it possible to migrate recipes from another plugin? =

Yes, our plugin includes an intuitive and easy-to-use tool that allows you to **import recipes** created using the WP Recipe Maker plugin.

[How to import recipes from other plugins](https://recipecard.io/features/importing-recipes-from-other-recipe-plugins/)


== Screenshots ==

1. Adding a recipe card to the editor
2. Recipe Card widget in Elementor
3. All blocks included
4. Preview of the Recipe Card Block
5. Additional Design for Block
6. Block Editor
7. Recipes Page
8. Mobile Design
9. Settings page


== Changelog ==

= 3.5.0 =
* New: Star ratings. Visitors can rate your recipes, and the rating is added to the recipe's structured data as `aggregateRating` so Google can show star ratings in search results.
* New: Comment ratings. Readers can leave a star rating along with their comment, and those reviews are added to the recipe schema.
* New: Ratings screen under Recipe Cards for approving, unapproving and deleting ratings.
* New: `[wpzoom_rcb_rating]` shortcode and a Recipe Rating block for showing the stars anywhere.
* New: Ratings settings tab, including the rating mode, star colour, and where the stars appear.
* New: Privacy policy suggestions, plus personal-data export and erase support for ratings.
* Note: This release adds a database table, `wpzoom_rating_stars`, and stores the IP address of each visitor who submits a rating in order to prevent duplicate votes. See the Ratings settings if you would rather not collect ratings at all.
* Fixed: Importing recipes from WP Recipe Maker no longer fails when the imported recipes have ratings.

= 3.4.19 =
* Minor bug fixes

= 3.4.18 =
* Minor bug fixes

= 3.4.17 =
* Minor bug fixes

= 3.4.16 =
* Improved: Full compatibility with WordPress 7.1
* Minor style fixes in the editor

= 3.4.15 =
* Multiple improvements and fixes

= 3.4.14 =
* Minor bug fixes

= 3.4.13 =
* Bug fix with the recipe importer

= 3.4.12 =
* Minor bug fix

= 3.4.11 =
* Minor bug fix

= 3.4.10 =
* Improvements to the Nutrition Facts block

= 3.4.9 =
* Minor bug fix

= 3.4.8 =
* Improvements to the Recipe AI Generator

= 3.4.7 =
* CSS bug fix

= 3.4.6 =
* Added a tooltip for cooking details.
* Fixed an issue with keywords in the recipe schema.

= 3.4.5 =
* Minor fixes

= 3.4.4 =
* Minor bug fixes

= 3.4.3 =
* Minor bug fix with the Ingredients and Directions blocks
* Added a new option to change the heading tag for recipe title.

= 3.4.2 =
* WordPress 6.7 notice fix
* Added a button to refresh the number of AI credits

= 3.4.1 =
* Added the Print button to recipes without an image
* Minor fixes

= 3.4.0 =
* NEW: Generate Recipes & Recipe Image using AI
* Multiple bug fixes

= 3.3.2 =
* Bug fix with the recipe importer

= 3.3.1 =
* Fixed a bug with the Schema markup in the Elementor widget
* Minor bug fixes

= 3.3.0 =
* New: Import recipes from WP Recipe Maker
* New: Create a Draft Post when creating a new recipe card post.

[See changelog for all versions](https://plugins.svn.wordpress.org/recipe-card-blocks-by-wpzoom/trunk/changelog.txt).

== Upgrade Notice ==

= 3.5.0 =
Adds star ratings, so your recipes can show star ratings in Google search results. This version creates a new database table and stores the IP address of visitors who submit a rating, in order to prevent duplicate votes. Ratings are on by default and can be turned off under Recipe Cards > Settings > Ratings.
