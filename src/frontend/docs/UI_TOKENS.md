# UI Tokens — Sapphire premium (light theme)

Design tokens for WorldOS frontend. Light theme only; no dark mode in scope.

## Palette

### Sapphire (primary)

| Token        | Hex       | Usage                    |
| ------------ | --------- | ------------------------- |
| sapphire-50  | `#eef4fc` | Subtle backgrounds       |
| sapphire-100 | `#d9e6f9` | Hover / disabled bg       |
| sapphire-200 | `#b8d0f2` | Borders, dividers         |
| sapphire-300 | `#8ab2e8` | Secondary buttons         |
| sapphire-400 | `#568eda` | Links, focus ring         |
| sapphire-500 | `#3470cc` | Ring, active states       |
| sapphire-600 | `#2657b0` | **Primary** (buttons, nav)|
| sapphire-700 | `#204690` | Primary hover             |
| sapphire-800 | `#1e3d76` | Primary active            |
| sapphire-900 | `#1d3562` | Headings accent           |
| sapphire-950 | `#142144` | Dark text on light        |

### Semantic

- **Success**: `#059669` (green-600)
- **Warning**: `#d97706` (amber-600)
- **Error**: `#dc2626` (red-600)

### Neutrals

- **Background**: `#f8fafc` (slate-50)
- **Foreground**: `#0f172a` (slate-900)
- **Muted**: `#f1f5f9` (slate-100)
- **Muted foreground**: `#64748b` (slate-500)
- **Border**: `#e2e8f0` (slate-200)

## Typography

- **Sans**: Geist (--font-geist-sans), fallback system-ui.
- **Scale**: `text-xs` meta, `text-sm` body, `text-base` lead, heading scale (h1–h4) from design system.
- **Line height**: default Tailwind; headings `leading-tight`.

## Spacing

Use Tailwind scale (1 = 0.25rem). Card padding `p-4` or `p-6`; section gap `gap-4` / `gap-6`.

## Components (shadcn)

- Primary button: `bg-primary text-primary-foreground` (sapphire-600).
- Inputs: border `border-border`, focus `ring-ring`.
- Cards: `bg-white border border-border rounded-lg`.

All tokens are wired in `src/app/globals.css` and Tailwind `@theme inline`.
