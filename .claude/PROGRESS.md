# RCB Growth Plan — Progress

_Started 2026-08-11. Strategy: fix the free/Pro split that drives churn to WP Recipe Maker, then make Pro sell services (AI, nutrition) instead of UX toggles._

**Context:** Free plugin: 10k installs, 20 reviews (4.8). WPRM: 50k+ installs, 354 reviews (5.0). Free RCB can't produce Google rich-result stars because ratings are Pro-gated → main churn driver.

## Workstreams

| # | Workstream | Status | Notes |
|---|-----------|--------|-------|
| 1 | Port comment/star ratings + Cook Mode → free | ✅ done (3.5.0, local) | Verified e2e on local site; needs code review + release |
| 2 | AI importer (URL/text/photo → recipe card), metered | 🟡 built, needs QA | Backend `/import_recipe` endpoint + editor UI done. Needs: in-browser editor QA w/ connected account, live AI round-trip test, commit to both wpzoom-openai branches. Metering = same credit pool for now (monthly free-import allowance would need backend credit-reset logic — open product decision) |
| 3 | Metered free auto-nutrition | ⏸ blocked on product decision | See "Open decisions" below |
| 4 | Recipe SEO Health dashboard | ✅ done (local) | `class-wpzoom-seo-health.php` — new "SEO Health" submenu; scans all `wpzoom_rcb` recipes (44 on dev site), 11 Google rich-result checks, click-to-fix links; verified via wp-cli |
| 5 | Triggered review-request flow | ✅ done (local) | `class-wpzoom-review-notice.php`: ≥5 published recipes + ≥7 days installed; snooze 30d / permanent dismiss; verified via wp-cli. Also rewrote stale recipes-page upsell copy (claimed free lacks Google stars — false after WS1) |
| 6 | Restructure free/Pro split + readme | ⏸ pending | Readme feature lists already updated for 1/2/4/5; full repositioning waits on metering decisions |
| 7 | wpzoom-openai security fixes | ✅ done (local, uncommitted) | Auth tokens issued at login + validated on AI endpoints (hash_equals); per-IP rate limiting (10/10min) on getUser/checkPurchase; nonce+cap check on AI settings form; `wp_edd_orders` prefix fix. Email fallback kept for old clients — sunset once 3.5.0 clients dominate (TODO in code) |

- **2026-08-11 (later)** — Browser QA of ratings with Pavel's Chrome: voting via comment form works e2e (4★ vote → "4.0 from 1 vote" + stars render on card front-end). Un-gated **Rating Stars Color** in settings and wired it to the free stars via `wp_add_inline_style` on the rating CSS handle (needs `!important` — rating.build.css has an `!important` rule; verified live with red). Note: voting happens in the comment form only — on-card instant voting is Pro (by design, mirrors WPRM); stars appear on a recipe only after its first vote.

- **2026-08-11 (later 2)** — **Pavel's call: on-card instant star voting is free too.** Ported `WPZOOM_Rating_Stars` from Pro (`class-wpzoom-rating-stars.php` + `wpzoom-rating-stars.js`): AJAX voting (`wpzoom_user_vote_recipe`, nonce-protected), cookie + IP/user duplicate prevention, Akismet hook, cache-purge integrations, AMP fallback. Free modes: instant (default) / jump_to_comments; **stored 'modal' from a Pro downgrade is normalized to instant** via `get_rating_mode()` (found live: this dev site had mode=modal → clicks were silent no-ops). Card heading now renders the interactive form (falls back to static average if only comment ratings on). Settings: User Rating + new mode select un-gated; Rating Modal section stays Pro. Marketing copy updated everywhere "Star Rating" was sold as Pro (settings sidebar, lite-vs-pro cards + comparison table now shows Star Rating ✓free/✓pro + new "Rating Modal & Written Reviews" ✕/✓ row, BF banner, recipes-page notice, readme). **Verified live in Chrome: clicked 5th star on card → AJAX → "5.0 from 1 vote" updated instantly.**

## Open decisions (need Pavel)

1. **Metering model for AI importer + nutrition (WS2/WS3):** current backend = 3 one-time free credits at store-site registration. The "5 free imports/month" pitch needs a monthly-reset allowance (new backend logic + product decision on limits). Currently the importer draws from the same credit pool.
2. **Should `regenerate_nutrition` start deducting credits?** Today it deducts nothing (backend quirk) — making free nutrition "metered" means introducing deduction, which also affects existing Pro users who currently get it free.
3. **WS6 (readme/pricing restructure)** ready to finish once 1–2 are decided.
4. **Release order**: everything is uncommitted working-tree changes (free plugin + wpzoom-openai @ main). wpzoom-openai auto-deploys on push (main→wpzoom.com, rcb-io→recipecard.io) — backend must deploy BEFORE free plugin 3.5.0 ships (client calls `/import_recipe`). The same patch applies cleanly to rcb-io (branch delta is only 2 EDD product IDs).

## Log

- **2026-08-11** — Market research done (wp.org stats, WPRM free/premium split, Food Blogger Pro criteria). Plan agreed. Task list created. 3 exploration agents launched: free plugin architecture, Pro ratings/cook-mode internals, wpzoom-openai backend.
- **2026-08-11** — wpzoom-openai backend mapped. Key facts for workstream 2/3:
  - Endpoints under `wp-zoom-openai/v1` (purchase/credits/regenerate_*) + `spoonacular/v1` (conversion, nutrition proxy). Auth = `validate_license_request()` (EDD license key, with legacy email/user_id fallback).
  - Credits: free tier = 3 credits on store-site registration (`{prefix}free_user_credits` by email); paid = `_user_credits` user meta. No rate limiting, no per-domain quota. Nutrition regenerate currently never deducts credits.
  - Models: chat `gpt-4o` (vision-capable — importer feasible), images default `gemini-2.5-flash-image`, DALL-E 3 fallback. Spoonacular already proxied for nutrition → workstream 3 has infra.
  - **No vision input, no URL fetcher exists yet** — importer needs: ingest path (URL fetch w/ SSRF protection, image upload), new route, new prompt, credit branch.
  - Branch delta main↔rcb-io = exactly 2 EDD product IDs (credits download: 791103 vs 576212; Pro product: 197189 vs 11). Deploy: main→wpzoom.com, rcb-io→recipecard.io.
  - ⚠️ **Security findings to fix while in there**: (1) legacy fallback lets anyone spend a customer's credits knowing only their email (client ships email/user_id, never license_key); (2) `/checkPurchase`/`/getUser` accept plaintext username+password; (3) AI settings form saves without nonce; (4) `order_item_details` hardcodes `wp_` table prefix.

- **2026-08-11** — Free plugin architecture mapped. Key facts:
  - Build: `cgb-scripts` (`npm run build` → dist/), PHP in `src/classes/` ships via .distignore filtering. phpcs via composer.
  - Schema: JSON-LD emitted by `WPZOOM_Recipe_Card_Block::get_json_ld()` in `src/structured-data-blocks/class-wpzoom-recipe-card-block.php` (~line 652). **No aggregateRating anywhere in free Gutenberg block**; Elementor widget has dead aggregateRating scaffolding (always stripped). Confirms the churn thesis.
  - Recipes stored as block attributes + mirrored to CPT `wpzoom_rcb` (`WPZOOM_Recipe_Post_Saver`) → SEO Health dashboard (WS4) can iterate the CPT.
  - Pro gating in settings is declarative per-field (`'disabled' => true` + PRO badge), incl. whole Ratings tab + prevent_sleep (cook mode) section. Runtime pro check = `class_exists('WPZOOM_Premium_Recipe_Card_Block')`; build-time `WPZOOM_RCB_HAS_PRO=false` in loader.
  - AI client flow: connect account (username/password → store `/getUser` → transient), then editor calls store `/purchase` with user_id+email (no token!). Credits mirrored in `wpzoom_credits` option; authoritative balance remote. `AIRecipeCredits` panel polls every 10s.
  - Review-ask infra: none (only static stars in plugins-row meta). Best template for dismissible notices: `class-wpzoom-recipes-page-notice.php` (per-user dismiss meta, min-recipes threshold, AJAX dismiss).
  - WPRM importer exists (`import/class-wpzoom-import-wprm.php`) but its rating import references `WPZOOM_Rating_DB` = Pro-only → ratings port (WS1) also fixes importer in free.

- **2026-08-11** — **Workstream 1 SHIPPED to working tree (v3.5.0)**. Comment ratings + Cook Mode now in free:
  - New: `src/classes/class-wpzoom-rating-db.php` (verbatim Pro port, shared table `{prefix}wpzoom_rating_stars` v1.2), `src/classes/class-wpzoom-comment-rating.php` (adds inline star SVGs + avg/votes/reviews helpers replacing Pro's `WPZOOM_Rating_Stars` deps), `templates/public/comment-rating{,-form}.php`, dist assets (rating css/js, nosleep.min.js).
  - Edits: loader requires; `class-wpzoom-recipe-card-block.php` (recipe_ID_rating static, `aggregateRating` in JSON-LD, average stars in heading, cook-mode toggle after details); Elementor widget aggregateRating wired; assets manager (nosleep register/enqueue, `defaultCookMode` localize); `script.js` NoSleep wiring; settings un-gated (comment_ratings, who_can_rate, cook mode ×3 + new `_toggle_status`); readme 3.5.0 + changelog; version bump.
  - **Gotcha fixed**: guarding a class with top-level `if (class_exists(SELF)) return;` fails — PHP early-binds unconditional classes at compile time, so the guard sees its own class and skips `init()`. Both ported classes now wrap the class *definition* in `if ( ! class_exists() ) { … init(); }`.
  - Build note: `npm run build` needs Node 14 (`~/.nvm/versions/node/v14.21.3/bin`) — node-sass breaks on arm64 Node 22. Rebuild reproduced committed dist byte-identical + new CSS.
  - Verified e2e via wp-cli on local site (post 7812): rated comment → row in table → `aggregateRating {ratingValue, ratingCount, reviewCount}` in JSON-LD → stars in card + comment + form; approval sync works; cook-mode toggle renders (setting left ON locally for review).
  - Kept in Pro: instant/modal star voting, stars color setting, rating modal section (mirrors WPRM free/premium split).

- **2026-08-11** — **Workstream 2 built** (pending QA):
  - Backend (wpzoom-openai, `main` working tree, uncommitted): new `POST wp-zoom-openai/v1/import_recipe` — source_type url/text/image; SSRF-protected URL fetcher (blocks private/reserved IPs, 3MB limit, verified vs 127.0.0.1/192.168.x/localhost/ftp); schema.org Recipe JSON-LD fast-path (verified on sallysbakingaddiction.com + recipetineats.com) with page-text fallback; base64 image validation (type+size, verified); strict "extract, don't invent" prompt reusing the generator's JSON schema; same free/paid credit deduction, logs generation_type `import`. Same patch applies to `rcb-io` branch (branch delta is only 2 EDD constants).
  - Client (free plugin): ButtonBox popover now has ✨Generate / 📥Import tabs; import panel with URL/Text/Photo sub-tabs, file→dataURL reader, `handleImportSubmit` → `/import_recipe` → `setRecipeData` fills the card; webpack build passes (blocks.build.js 210→211KB).
  - rcb.dev local WP has no working DB connection from wp-cli (tested helpers by requiring the class from the rcb site instead).

## Decisions

- Free tier = everything readers touch; Pro = growth features + metered AI services.
- AI importer metering: ~5 free imports/month, unlimited with Pro license.
- All wpzoom-openai backend changes must land on BOTH branches (wpzoom + recipecard.io; differ only in EDD product ID + site URL header).
