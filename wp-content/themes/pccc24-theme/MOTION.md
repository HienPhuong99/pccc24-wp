# MOTION.md: living illustrations in this project

> This file documents the `rossark-motion-skill` method (Part 1) and the PCCC24 WordPress theme specifics (Part 2).

---

## Part 1. The method

**The goal:** anything drawn (a character, a plant, a flame, a street, a logo, a chart) feels alive and calm. Nothing teleports, nothing bounces, nothing shouts.

### Principles
1. **Never teleport:** interpolate everything; never swap drawings.
2. **Calm by default:** strong curves, no bouncy overshoot.
3. **Small amplitudes:** when unsure, halve it.
4. **Arcs and pivots:** each moving part is a layer rotating around its joint (stem base, hinge, neck, hook).
5. **Overlap and follow-through:** tips and trailing parts move later and further; give each part the same wave with a growing phase delay.
6. **Variety without chaos:**
   - repeated items get a seeded phase, speed and size;
   - nothing moves in sync, and there's no random value per frame.
7. **One main action per scene, plus two or three idle loops.** Side things stay quiet.
8. **Opacity only to appear or disappear.**
9. **The layout never moves:** motion lives inside the drawing.
10. **Reduced motion:** loops freeze on a good frame 0, and one-shots show their final frame.
11. **Nothing runs unseen:** pause off screen, when hidden and in the background.
12. **Enough frames:** anything under ~100 ms flickers at 30 fps; render and export at 60 when you can. Blinks close in ~110 ms and reopen in ~190 ms.
13. **Interface rules:**
    - frequency decides how much (often seen = barely moves; rare moments get the delight);
    - every motion has a purpose;
    - shared elements travel, never teleport;
    - interface moves stay under 300 ms;
    - exits are faster than entrances;
    - nothing appears from scale 0;
    - interruptions continue from the current value.

### The engine
- **Clocks:** a loop clock for idle life, a one-shot clock for a scene that tells an action. One per character, group or scene.
- **Tracks:** keyframes `[ms, value, 'out'?]` read with `track(t, keys)`. The last key equals the first. `track` stops at every key, so use it for one move (a blink, a lid). For acting (a chain of poses), use `flow(t, keys)`, which passes through the keys without stopping and never overshoots.
- **Helpers:**
  - `wave` (−1 to 1), for sway and breathing;
  - `loop` (0 to 1), for drift and falling;
  - `bell`, to fade in and out;
  - `seg`, for one beat of a one-shot;
  - `seeded(i)`, for per-item variety.
- **Curves:**
  - travel uses ease-in-out `(0.77, 0, 0.175, 1)`;
  - open and arrive use ease-out `(0.23, 1, 0.32, 1)`;
  - leaving uses ease-in;
  - endless loops are linear.

### Quick recipes
| Subject | Recipe |
|---|---|
| Character | breathing 1–2 units over 3–5 s; a blink every 3–4 s; pupils glide; head tilt 3–6° |
| Waiting to be tapped | a jiggle every ~270 ms: squash 2%, widen 2%, the bulge trading sides |
| Floating figure | rises in from 30% of its height below, drifts 5% down and up, leaves rising |
| Tail, hair, ears | rotation around the root, phase delayed along the chain, 4–10° |
| Plant | sway 1.5–4° around the base over 3–6 s; leaves on seeded phases |
| Cloth, flag | a wave that travels along the length, growing away from the fixed edge |
| Fire | a lean of 2–4° and a 3–6% stretch on two waves of different periods |
| Smoke, steam | rises 8–14 units and fades in and out with `bell` |
| Water | ripples grow and fade; floating things bob 1–2 units and rock 2° |
| Rain, snow | seeded `loop`s; snow sways sideways |
| Sky | clouds drift over 20–60 s with parallax; sun rays take 30–60 s per turn |
| Lights | a glow of 2–5% over 2–4 s; stars twinkle at seeded times |
| Machines | gears turn linearly by ratio; a clock hand steps with ease-out |
| Phone | ±3° bursts, then a pause |
| Icon, logo | plays once on appear, then rests |
| Chart | a one-shot draw-on, with bars staggered 40–80 ms |

### Acting (a whole character)
- **Every channel:** key pose (`flow`) + idle (`wave`s, never off while on stage) + follow-through (the parent read 90–140 ms earlier) + accents (a `wave` inside a `seg` window).
- **Arms:** an upper arm and a forearm share a round elbow; the forearm points at shoulder angle + elbow bend.
- **Head turns:** slide the face toward the turn (eyes 10, nose 13 in a 100-wide head) and the ears the other way. Blink on the turn.
- **Weight:** the hips shift and carry the upper body, and the legs angle to keep the feet under it.
- **Landing:** hover, then fall with gravity and stretch; squash hard (0.8) on contact and keep the volume (`width = 1 / height^0.6`).
- **In the air:** limp feet (toes down, one knee folded, trading slowly). The shadow stays on the floor and scales with the height.
- **Props:** enter from off screen already moving and roll by distance / radius. The eyes follow them. A kick hits at the middle of the swing; the prop leaves as a throw, spinning, with a short squash.
- **Asymmetry:** the arms never mirror each other.
- **Camera:** a view from a little above is a whole build, not a tweak: the head is 40–50% of the height, the torso is short and narrower than the head, the legs are short, the feet are round from above, and the shadow is a wide oval with the feet in its lower half. Keep it in one `build` object. A flat view keeps a thin pill shadow.
- **Framing:** on a phone, as big as the screen allows, with the feet low.

### Performance
- Everything is a function of one clock's time.
- Draw many items in one SVG or `Canvas`.
- Reset repeating animations to 0 before starting, and cancel them on cleanup.
- Declare curves at the top of the module.

### Workflow
1. Read Part 2 and the art file's header comments.
2. List what could live in the drawing.
3. Pick one main action and two or three idle loops.
4. Split the art into layers at their pivots.
5. Put the numbers in tokens.
6. Check frame 0, the final frames and the loop seams.
7. Run the checks.
8. Hand off: what moves, and what hasn't been seen on a real device.

---

## Part 2. This project (`pccc24-theme`)

### Where the code is
- **Platform:** Web (WordPress theme `wp-content/themes/pccc24-theme/`, pure vanilla JS + SVG + CSS, zero external JS frameworks).
- **Motion engines and live rigs:**
  1. **User Frontend Engine (`wp-content/themes/pccc24-theme/assets/js/site.js`):** Implements `window.PCCC24_MOTION` (`EASE_IN_OUT`, `EASE_OUT`, `track`, `flow`, `seg`, `wave`, `loop`, `bell`, `seeded`) with a single `requestAnimationFrame` clock, `IntersectionObserver`, `document.hidden`, and `prefers-reduced-motion: reduce` support.
  2. **Admin V18 Engine (`wp-content/themes/pccc24-theme/assets/js/admin-motion.js`):** Implements `window.PCCC24_ADMIN_MOTION` for WordPress `/wp-admin/` and static GitHub Pages `/wp-admin/`.
- **Art kits, layers, pivots:**
  - **User Frontend (`wp-content/themes/pccc24-theme/template-parts/quote-tag.php`):** The iOS-inspired fire-extinguisher inspection card (`.tag`) with an embedded living SVG pressure-gauge & valve-lever inspection rig (`.tag-gauge-scene`, `viewBox="0 0 206 50"`):
    - **Tag body (`.tag`):** Pivots around the top cord grommet `(50%, 14px)`, settling with `flow` (`[[0, -1.2], [520, 0.18], [1200, 0]]`) + calm `wave` sway (`±0.32°`).
    - **Gauge needle (`.rg-needle`) & Glow (`.rg-glow`):** Pivot around `(30px, 25px)`. One-shot sweep `-52° -> 0°` (`seg` + `EASE_OUT`) + `±1.6°` idle breathing.
    - **Safety lever (`.rg-lever`) & Safety pin (`.rg-pin`):** Lever pivots around `(80px, 20px)` and pin around `(73px, 22px)` with phase-delayed follow-through (`-0.16` phase).
    - **Mist/foam safety wisps (`.rg-wisp`):** 3 seeded particles rising from the nozzle using `loop`, `bell`, and `seeded(i)`.
    - **Status LED (`.rg-led`):** Pulses gently around `(144px, 25px)`.
    - **Floating Contact Phone Icon (`.float-contact .btn-call svg`):** Follows the Phone recipe (`±3°` bursts, then a pause).
  - **Admin Dashboard V18 (`functions.php` & `scripts/export-gh-pages.php`):** Living Telemetry & Fire-Safety Rig (`.v18-motion-scene`, `viewBox="0 0 280 56"`) inside `.v18-hero-card[data-v18-motion]`:
    - **Gauge & Valve Layers:** `.v18-glow` and `.v18-needle` pivot at `(30px, 28px)`, `.v18-lever` at `(84px, 22px)`, `.v18-pin` at `(76px, 24px)`, `.v18-led` at `(142px, 18px)`, plus 3 `.v18-wisp` particles.
    - **Chart Recipe (`.v18-bar`):** 5 telemetry bars (`transform-box: fill-box; transform-origin: 50% 100%`) with a one-shot draw-on staggered `55ms` (`seg(oneShotTime, 90 + b*55, 760 + b*55, EASE_OUT)`) from `scaleY(0.18)` to `scaleY(1)` + subtle idle wave breathing.
- **Interface motion (`assets/css/site.css`, `assets/css/ios-sheet.css`, `assets/css/admin.css`):**
  - Curves declared as CSS custom properties `--ease-in-out: cubic-bezier(0.77, 0, 0.175, 1)` and `--ease-out: cubic-bezier(0.23, 1, 0.32, 1)`.
  - **Traveling Shared Elements (Principle 13: *shared elements travel, never teleport*):**
    - `.seg-pill` glides smoothly (`translate3d` + `width` over `220ms var(--ease-in-out)`) across `.seg-control` on the homepage while `.featured-pane.pane-enter` enters from `scale(0.985), opacity: 0` (`180ms var(--ease-out)`).
    - `.v18-nav-pill` glides across the static Admin V18 sidebar dock (`.v18-nav`).
  - **Bento Card Staggered Entrance:** `.v18-bento-wrap > *` and `.v18-bento-grid > *` enter with a `45ms` stagger (`calc(var(--stagger-i, 0) * 45ms)`) from `translate3d(0, 8px, 0) scale(0.975)` (`280ms var(--ease-out)`).
  - **Bottom Sheet (`dialog.ios-sheet[open]`):** Enters from `translate3d(0, 36px, 0) scale(0.95)` (never from `scale(0)`).

### The owner's rules
- **2026-10-31:** iOS-inspired user frontend (`design_handoff_pccc24_ios`) + Dashboard V18 admin (`V18.mp4`) + `rossark-motion-skill` motion system across both user and admin.
- **2026-10-31:** Zero horizontal overflow on any viewport (`documentElement.scrollWidth <= clientWidth`). Motion lives inside the SVG and traveling pills without ever shifting layout or causing horizontal scrollbar overflow.

### Rig limits
- Product photos in `.grid-products` are flat raster images from the original database (`wp-content/uploads/pccc24/`), so living motion is focused on the vector SVG inspection gauge scenes (`.tag-gauge-scene` on user frontend, `.v18-motion-scene` on admin V18), floating phone icon bursts, traveling segmented pills, and calm interface transitions.

### How changes are checked
- Automated Playwright E2E suite (`npx playwright test`, 71 tests across `pccc24-wp.spec.ts`, `ui-audit.spec.ts`, and `admin-crud.spec.ts`), including axe-core WCAG checks, horizontal overflow checks, zero console/page errors, REST API checks, and `wp-admin` CRUD tests.
- Data integrity check: `php tools/wp-cli.phar eval-file scripts/verify-migration.php` (15/15 checks).
