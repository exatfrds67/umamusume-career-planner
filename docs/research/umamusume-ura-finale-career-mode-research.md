# Umamusume: Pretty Derby - URA Finale Career Mode Research (Global EN)

Research date: March 25, 2026  
Scope: Global English version behavior first, with JP-only notes clearly separated.

---

## Research Method

This document was rebuilt using multi-source verification.

- Primary official source set:
   - Umamusume English official site (game overview/news shell): https://umamusume.com/
- High-trust third-party source set (global-focused):
   - Game8 global Umamusume wiki and URA pages
   - GameTora scenario and onboarding guides
- Supplemental source set:
   - uma.guide index pages for tooling/context

Notes:
- Direct deep crawling of umamusu.wiki subpages is blocked by robots rules for this user agent; only the main page was accessible in tooling.
- Official English news list content is JS-populated at runtime; metadata and page shell were verifiable, but full item text was not directly extractable with this fetch path.

---

## Executive Summary

FINDING: URA Finale in Global is the baseline, goal-driven, three-year scenario where players must clear character goals and then win three URA Finals races (qualifier, semifinal, final) to complete a top-end run.

Confidence: VERIFIED (3+ sources)

- Per Game8 URA Scenario Guide (updated 2025-08-22): URA has fixed event windows, fan-threshold unique level-ups, Summer Camp Lv5 training windows, and three URA final races.
- Per Game8 Career Scenario Guide (updated 2026-03-16): URA is the first permanent scenario in Global with 1200 stat caps and core scenario loop.
- Per GameTora URA Finale Scenario (updated 2023-01-19, with global notes): URA is objective-gated, uses facility leveling by repeated training, and has global-specific cap notes (1200 then future rebalance).

---

## Question 1/1: Detailed URA Finale Research (Global EN)

### 1. Scenario Identity and Global Positioning

FINDING: URA is the foundational scenario layer in Global, and later scenarios are framed as extensions or alternatives to its loop.

Confidence: VERIFIED (2+ sources)

- Per Game8 Career Scenario Guide (2026-03-16): URA is the first permanent scenario and teaches the basic flow that later scenarios build upon.
- Per Game8 URA Guide (2025-08-22): URA was the initial Global career mode at launch period and is described as the straightforward baseline.
- Per official English site gameplay copy (accessed 2026-03-25): the game centers on choosing training regimens and raising trainees through structured training/racing progression.

Practical implication:
- URA planning is still useful even when newer scenarios exist, because core decision-making (energy, mood, training density, fan thresholds, race prep) carries forward.

### 2. Core Progression Model

FINDING: URA progression is objective-gated and fail-state driven.

Confidence: VERIFIED (2 sources)

- Per GameTora URA Scenario (2023-01-19): character-specific objectives (race placements, participation, fan counts) gate advancement.
- Per GameTora Beginner Guide (2025-06-26): failing required goals ends the run.

What this means in practice:
- You cannot optimize purely for stats; timing and completion of mandatory targets are non-negotiable.
- Calendar planning is mandatory, not optional, for consistency.

### 3. URA Finals Structure

FINDING: The end of URA is a three-race sequence.

Confidence: VERIFIED (2 sources)

- Per Game8 URA Fixed Events Calendar (2025-08-22): explicit post-race rewards are listed for qualifier, semifinal, and final.
- Per GameTora URA Basic Information (2023-01-19): clear qualifiers to reach semifinals, then final.

Planner consequence:
- Build should peak before late Senior year, not at the exact final turn.
- Skill-point banking before finals is important because race rewards scale and endgame purchases can decide consistency.

### 4. Facility Leveling and Training Windows

FINDING: URA rewards repeated facility specialization early, then rainbow/friendship exploitation later.

Confidence: CONSENSUS (3 sources)

- Per Game8 URA Guide (2025-08-22): repeating the same training 4 times raises facility level.
- Per GameTora URA Guide (2023-01-19): facilities level from 1 to 5 with repeated use (every four uses).
- Per GameTora New Player FAQ (2025-06-28): early priority is friendship gauge and facility progression before pure stat greed.

Summer Camp windows (Global-aligned community consensus):
- Per Game8 URA calendar (2025-08-22): Year 2 and Year 3 summer blocks provide Lv5 facility windows across four turns.

Execution pattern:
- Early game: secure bond and set future high-value lanes.
- Mid game: transition into friendship/rainbow burst turns.
- Summer blocks: reserve best energy/mood turns for stacked support training.

### 5. Unique Skill Level-Ups and Fan Threshold Logic

FINDING: Unique level-ups are strongly tied to fixed fan gates and one director relationship gate.

Confidence: VERIFIED (2 sources)

- Per Game8 URA Guide (2025-08-22):
   - Early Feb Senior: 60,000 (Turf) / 40,000 (Dirt)
   - Early Apr Senior: 70,000 (Turf) / 60,000 (Dirt) plus green friendship (3 bars) with Director Akikawa
   - Late Dec Senior: 120,000 (Turf) / 80,000 (Dirt)
- Per GameTora URA Guide (2023-01-19): same three timing gates and Turf/Dirt split.

Planning consequence:
- Optional race insertion must be controlled by fan-threshold checkpoints, not just by skill-point hunger.
- Director bond is a hard dependency for the April level-up path.

### 6. Mood, Stamina, and Failure Prevention

FINDING: Mood and stamina are the two most common run killers in URA for otherwise decent builds.

Confidence: CONSENSUS (3 sources)

- Per Game8 URA Guide (2025-08-22): Great mood increases training gains and race performance; summer optimization advice emphasizes full energy and good mood.
- Per Game8 URA Guide (2025-08-22): notes stamina shortfall as a common URA finale failure reason.
- Per GameTora New Player FAQ (2025-06-28): provides practical stamina threshold ranges by distance and notes style/condition modifiers.

Operational guidance:
- Guardrail 1: never enter key goals on red energy.
- Guardrail 2: before long-distance checkpoints, treat stamina as pass/fail, not a luxury stat.
- Guardrail 3: if no high-value training appears, race/Wit/restore based on immediate threshold pressure.

### 7. Stat Caps and Version Nuance

FINDING: Global URA cap baseline is 1200 per core stat in the current mainstream Global guidance; JP-side rebalance history exists and can cause confusion.

Confidence: VERIFIED (2 sources)

- Per Game8 Career Scenario Guide (2026-03-16): URA listed at 1200 cap across core stats in Global.
- Per GameTora URA Guide (2023-01-19): notes Global as 1200 at that point, with mention of later cap rebalance context.

Important caveat:
- JP and Global timelines are not synchronized. Any 1400-cap references should be treated as version-contextual unless confirmed in current Global patch notes.

### 8. Scenario Link and Character-Specific Details

FINDING: Aoi Kiryuin is a URA scenario-link axis, and some event outcomes are strengthened when her support is present.

Confidence: VERIFIED (2 sources)

- Per Game8 URA Guide (2025-08-22): identifies Aoi Kiryuin as scenario link and recommends early-account use.
- Per GameTora URA Guide (2023-01-19): documents scenario-link interaction and event outcome strengthening.

Practical impact:
- Early rosters can leverage scenario-link consistency in URA to stabilize progression while support pools are still weak.

### 9. Global-Confirmed vs JP-Only Notes

FINDING: Some URA features discussed in broader communities are JP-first and should be flagged when building Global planner logic.

Confidence: VERIFIED (2 sources)

- Per GameTora URA Guide (2023-01-19): Happy Meek duel system is marked as JP update and explicitly noted as not yet on Global at that time.
- Per Game8 global scenario pages (2025-2026): global-facing URA calendars and system framing do not center this mechanic.

Recommendation for product docs:
- Mark these as "server/version-conditional" and avoid hard-coding into Global-default flow until explicitly confirmed in current EN patch notes.

---

## Advanced Planning Framework for URA (Global)

This section converts source findings into a practical run framework.

### A. Turn-Phase Priority Model

1. Junior to early Classic:
- Prioritize bond development and facility level progression.
- Do not over-race unless fan thresholds require it.

2. Mid Classic to early Senior:
- Shift toward friendship/rainbow conversion turns.
- Start race insertion for fan gates and skill-point reserve.

3. Senior pre-finals:
- Lock in unique level-up gates and director bond requirement.
- Ensure stamina floor and key skills before qualifier chain.

### B. Decision Heuristics

- If rainbow/friendship training appears with acceptable energy: take it.
- If no high-value training and fan gate is behind: race.
- If no high-value training and fan gate is safe: Wit or recovery.
- If mood drops before major checkpoints: recover mood proactively.

### C. High-Risk Failure Points

- Missing fan gate windows for unique upgrades.
- Entering long races with insufficient stamina.
- Overcommitting to one stat while ignoring mandatory race profile needs.
- Burning premium training opportunities outside Summer Camp windows.

---

## Planner Integration Recommendations (for this repository)

These are implementation-relevant items for the app.

1. Goal race timeline lock-in:
- Add goal-critical warnings 6/3/1 turns ahead of deadline races.

2. Unique gate tracker:
- Live panel for current fans vs next gate, plus director bond state for April gate.

3. Summer Camp optimizer:
- Mark pre-camp "setup turns" and predict value delta for waiting vs spending now.

4. Recovery risk monitor:
- Show stamina risk by target race distance and style with configurable safety margins.

5. Version flagging:
- Keep per-server feature toggles for JP-only/Global-pending mechanics.

---

## Source Matrix

Official:
- Umamusume Official English Site (Cygames), accessed 2026-03-25
   - https://umamusume.com/
   - https://umamusume.com/news

Third-party (global-focused, high utility):
- Game8: URA Finale Scenario Guide (updated 2025-08-22)
   - https://game8.co/games/Umamusume-Pretty-Derby/archives/536520
- Game8: Career Scenario Mode Guide (updated 2026-03-16)
   - https://game8.co/games/Umamusume-Pretty-Derby/archives/536350
- GameTora: URA Finale Scenario (updated 2023-01-19)
   - https://gametora.com/umamusume/ura-finals
- GameTora: Uma Musume Guide For Absolute Beginners (updated 2025-06-26)
   - https://gametora.com/umamusume/beginners-guide
- GameTora: New Player FAQ (updated 2025-06-28)
   - https://gametora.com/umamusume/guides/new-player-faq
- GameTora: Global Quickstart Guide (created 2025-06-26)
   - https://gametora.com/umamusume/guides/global-quickstart-guide

Supplemental:
- uma.guide index/home (accessed 2026-03-25)
   - https://uma.guide/

---

## Confidence and Discrepancy Notes

- High-confidence global URA core loop items: objective-gated progression, 3-race URA final chain, Summer Camp Lv5 windows, fan-threshold unique upgrades.
- Moderate-confidence items requiring version caution: JP-advanced mechanics and post-rebalance cap discussions.
- Known extraction limitation: official news list body content is runtime-loaded and was not fully extractable through direct static fetch; third-party global sources were used to fill current operational details.

---

## Bottom Line

For the Global English environment today, URA should be treated as a deterministic objective-and-threshold management scenario: plan calendar goals first, convert midgame into friendship-driven stat bursts, hit fan/director gates on time, and enter the qualifier-semifinal-final chain with stamina-safe race profiles.
