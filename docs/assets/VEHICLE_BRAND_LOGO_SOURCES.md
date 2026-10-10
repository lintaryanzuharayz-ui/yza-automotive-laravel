# Vehicle brand logo sources

All files listed below are stored locally under `public/assets/images/brands/` so the detail pages never hotlink brand artwork at runtime. SVG files were checked for script/event-handler content before being used.

Captured: 2026-10-04

| Brand | Local file | Source URL | Source type |
|---|---|---|---|
| Aston Martin | `assets/images/brands/aston-martin.svg` | https://cdn.jsdelivr.net/npm/simple-icons@v15/icons/astonmartin.svg | Simple Icons SVG brand asset |
| Audi | `assets/images/brands/audi.svg` | https://cdn.jsdelivr.net/npm/simple-icons@v15/icons/audi.svg | Simple Icons SVG brand asset |
| BMW | `assets/images/brands/bmw.svg` | https://cdn.jsdelivr.net/npm/simple-icons@v15/icons/bmw.svg | Simple Icons SVG brand asset |
| Bugatti | `assets/images/brands/bugatti.svg` | https://cdn.jsdelivr.net/npm/simple-icons@v15/icons/bugatti.svg | Simple Icons SVG brand asset |
| Chevrolet | `assets/images/brands/chevrolet.svg` | https://cdn.jsdelivr.net/npm/simple-icons@v15/icons/chevrolet.svg | Simple Icons SVG brand asset |
| Daihatsu | `assets/images/brands/daihatsu.svg` | https://commons.wikimedia.org/wiki/File:Daihatsu_Logo.svg | Wikimedia Commons SVG; author listed as Daihatsu |
| Dodge | `assets/images/brands/dodge.svg` | https://commons.wikimedia.org/wiki/File:Dodge_2022_logo.svg | Wikimedia Commons SVG |
| Ferrari | `assets/images/brands/ferrari.svg` | https://cdn.jsdelivr.net/npm/simple-icons@v15/icons/ferrari.svg | Simple Icons SVG brand asset |
| Ford | `assets/images/brands/ford.svg` | https://cdn.jsdelivr.net/npm/simple-icons@v15/icons/ford.svg | Simple Icons SVG brand asset |
| Honda | `assets/images/brands/honda.svg` | https://cdn.jsdelivr.net/npm/simple-icons@v15/icons/honda.svg | Simple Icons SVG brand asset |
| Lamborghini | `assets/images/brands/lamborghini.svg` | https://cdn.jsdelivr.net/npm/simple-icons@v15/icons/lamborghini.svg | Simple Icons SVG brand asset |
| Lexus | `assets/images/brands/lexus.svg` | https://commons.wikimedia.org/wiki/File:Lexus_Logo.svg | Wikimedia Commons SVG; current Lexus emblem |
| McLaren | `assets/images/brands/mclaren.svg` | https://cdn.jsdelivr.net/npm/simple-icons@v15/icons/mclaren.svg | Simple Icons SVG brand asset |
| Mercedes-Benz / Mercedes-AMG | `assets/images/brands/mercedes-amg.svg` | https://commons.wikimedia.org/wiki/File:Mercedes-Benz_logo.svg | Wikimedia Commons SVG; Mercedes-Benz wordmark |
| Mitsubishi | `assets/images/brands/mitsubishi.svg` | https://cdn.jsdelivr.net/npm/simple-icons@v15/icons/mitsubishi.svg | Simple Icons SVG brand asset |
| Nissan | `assets/images/brands/nissan.svg` | https://commons.wikimedia.org/wiki/File:Nissan_2020_logo.svg | Wikimedia Commons SVG; Nissan 2020 logo |
| Porsche | `assets/images/brands/porsche.svg` | https://cdn.jsdelivr.net/npm/simple-icons@v15/icons/porsche.svg | Simple Icons SVG brand asset |
| Suzuki | `assets/images/brands/suzuki.svg` | https://cdn.jsdelivr.net/npm/simple-icons@v15/icons/suzuki.svg | Simple Icons SVG brand asset |
| Toyota | `assets/images/brands/toyota.svg` | https://cdn.jsdelivr.net/npm/simple-icons@v15/icons/toyota.svg | Simple Icons SVG brand asset |

## WhatsApp CTA asset

`public/assets/images/social/whatsapp.svg` is a local SVG from the Simple Icons WhatsApp asset, sourced from the WhatsApp brand mark and used only as the green icon inside the YZA red CTA. The application does not load it from a remote URL.

Source: https://cdn.jsdelivr.net/npm/simple-icons@v15/icons/whatsapp.svg

## Mapping

`App\\Support\\BrandLogo` remains the single mapping used by both catalog cards and all `/koleksi/{slug}` detail pages. It normalizes the database brand name and selects the corresponding local SVG; unknown brands use the local YZA fallback rather than a remote image.
