# Umamusume: Pretty Derby - Unity Cup Career Mode Research (Global EN)

Research date: March 25, 2026  
Scope: Global English implementation first, with JP-only details explicitly isolated.

---

## Research Method

This document was rebuilt through multi-source verification.

- Official/global-facing source layer:
   - Umamusume official English web properties (site shell and game framing): https://umamusume.com/
   - Global announcement text as published on umamusume.gg news mirror pages
- High-trust third-party mechanics layer:
   - Game8 Global Unity Cup and scenario pages
   - GameTora Unity Cup scenario guide and related global guides

Limitations:
- The official English news page is runtime-rendered and did not expose full news items through static fetch in this environment.
- Where official news text was needed, mirrored publication pages were used and labeled as such.

---

## Executive Summary

FINDING: Unity Cup (Aoharu Hai) in Global EN is a team-race scenario layered on top of standard three-year career progression, where success depends on team development, Spirit Burst timing, and regular six-month team race checkpoints.

Confidence: VERIFIED (3+ sources)

- Per Game8 Unity Cup Guide (updated 2026-03-08): Unity Cup is the second permanent scenario in Global, with scheduled team races and Spirit Burst-centered progression.
- Per Game8 Career Scenario Guide (updated 2026-03-16): Unity Cup retains career-goal structure while adding team-race and training-level differences.
- Per GameTora Unity Cup Scenario (updated 2026-01-21): Unity Cup keeps URA-style core flow but introduces team members, special training, Spirit Explosion, and team-rank-linked progression.

---

## Question 1/1: Detailed Unity Cup Research (Global EN)

### 1. Scenario Positioning and Release Context

FINDING: Unity Cup is the second permanent Global scenario and was released in Global on November 6, 2025.

Confidence: VERIFIED (3 sources)

- Per Game8 Unity Cup Guide (2026-03-08): release date listed as November 6, 2025 UTC.
- Per Game8 Career Scenario Guide (2026-03-16): Unity Cup documented as second permanent scenario in Global.
- Per umamusume.gg mirrored notice "The new Career scenario 'Unity Cup: Shine On, Team Spirit!' is here!" (updated 2025-11-06): confirms scenario launch timing and entry point in Career menu.

### 2. Core Structural Difference from URA

FINDING: Unity Cup still uses trainee goals and endgame URA finals chain, but adds recurring team tournaments and team-progression systems.

Confidence: VERIFIED (2+ sources)

- Per Game8 Unity Cup Guide (2026-03-08): still requires trainee race-goal completion and includes team race structure every six months.
- Per GameTora Unity Cup Guide (2026-01-21): schedule/objective skeleton remains URA-like, while team-race mechanics are the major extension.

Practical implication:
- Unity Cup is not a fully sandbox scenario; missing core trainee goals remains run-ending.

### 3. Team Races and Calendar Rhythm

FINDING: Team races are periodic checkpoints, typically every six months (late June/late December cadence), with multiple rounds before finals.

Confidence: CONSENSUS (3 sources)

- Per Game8 Unity Cup Guide (2026-03-08): lists Round 1 to Finals schedule across Junior/Classic/Senior checkpoints.
- Per Game8 Career Scenario Guide (2026-03-16): describes six-month Aoharu/Unity race cadence.
- Per umamusume.gg mirrored announcement (2025-11-06): states Unity Cup consists of five showdowns (four preseason plus finals).

Operational consequence:
- Team readiness is a recurring requirement, not just end-of-career optimization.

### 4. Team Composition and Growth Model

FINDING: Team roster includes trainee + support-linked members + scenario/story/random recruits, and team strength directly affects scenario progression quality.

Confidence: VERIFIED (2 sources)

- Per Game8 Unity Cup Guide (2026-03-08): team building spans five race categories, with assignment by aptitudes and rank quality.
- Per GameTora Unity Cup Guide (2026-01-21): team members come from support deck (excluding pal), scenario characters, and additional recruits over time.

Important nuance:
- Only normal support cards maintain standard bond-gauge friendship behavior; non-support recruits contribute to Unity systems but not normal rainbow training.

### 5. Unity Training and Spirit Burst Mechanics

FINDING: Unity Training increases team-member growth, while Spirit Burst provides additive trainee/team gains plus skill hints.

Confidence: VERIFIED (2 sources)

- Per Game8 Unity Cup Guide (2026-03-08): details Unity Training participant-count bonuses and Spirit Burst stacking behavior.
- Per GameTora Unity Cup Guide (2026-01-21): details Spirit Explosion gains, hint behavior, additive stacking, and energy-impact behavior.

Convergent points across both sources:
- Multi-participant Unity events are high-value turns.
- Spirit Burst timing can be delayed to preferred facility types.
- Burst effects are additive, not multiplicative.

### 6. Training Facility Level Logic (Major Change)

FINDING: Unity Cup training facility level does not primarily scale by repeated use; it scales with team stat rank progression.

Confidence: VERIFIED (2 sources)

- Per Game8 Unity Cup Guide (2026-03-08): facility level tied to team stat level/rank.
- Per GameTora Unity Cup Guide (2026-01-21): facility levels map to team stat rank bands (e.g., F/G to S).

Practical effect:
- "Solo lane spam" is weaker than in URA when it ignores team-rank growth needs.

### 7. Unique Skill Level-Ups and Fan Gates

FINDING: Unity Cup unique-skill fan thresholds largely mirror URA timing, with one key difference around April requirements.

Confidence: VERIFIED (2 sources)

- Per GameTora Unity Cup Guide (2026-01-21): fan thresholds align with URA pattern; April gate does not require Akikawa bond because she is absent in this scenario.
- Per Game8 Unity Cup Guide (2026-03-08): fan-threshold and scenario event gating still matter for run quality and skill outcomes.

Implication:
- Fan gate planning remains mandatory, even though team systems are the headline mechanic.

### 8. Scenario Links and Global-Relevant Bonuses

FINDING: Scenario-linked characters/supports influence Unity Cup outcomes, including stronger burst/training-related value in supported contexts.

Confidence: VERIFIED (2 sources)

- Per Game8 Career Scenario Guide (2026-03-16): lists Unity Cup scenario-linked entities.
- Per Game8 Unity Cup Guide (2026-03-08) and GameTora Unity Cup Guide (2026-01-21): both describe practical effects from scenario-linked participants in burst/training outcomes.

Planning impact:
- Early-account deck planning in Unity Cup benefits from scenario-link-aware support selection, especially when consistency matters more than ceiling.

### 9. Stat Caps and Version Nuance

FINDING: Global-facing guidance treats Unity Cup caps as 1200/1200/1200/1200/1200 in current mainstream sources, while JP-side values and updates differ.

Confidence: VERIFIED (2 sources)

- Per Game8 Career Scenario Guide (2026-03-16): Unity Cup shown with 1200 baseline caps in Global.
- Per GameTora Unity Cup Guide (2026-01-21): distinguishes JP values/updates from Global expectations.

Recommendation:
- Keep stat-cap logic version-aware and avoid assuming JP rebalance values on Global without explicit confirmation.

### 10. Finals and Endgame Impact

FINDING: Unity Cup performance influences end-of-run quality and can affect final-race context/rewards through team-rank progression and event outcomes.

Confidence: VERIFIED (2 sources)

- Per Game8 Unity Cup Guide (2026-03-08): advises rank progression thresholds for stronger final consistency.
- Per GameTora Unity Cup Guide (2026-01-21): explains Zenith finals context and interactions with subsequent race contexts.

---

## Global-Confirmed vs JP-Only Notes

Global-confirmed (high confidence):
- Unity Cup is permanent and selectable from Career mode.
- Five-showdown structure (preseason rounds + finals).
- Team-rank-linked facility progression.
- Spirit Burst/Unity Training as central optimization loop.

JP-only or version-conditional (mark as conditional in planner docs):
- 2023 JP "Aoharu scenario update" details such as Zenith Spirit Explosion extensions and certain post-update rule changes.

Sources:
- GameTora Unity Cup Guide (JP update section, 2026-01-21)
- Game8 Global pages (2026) for current global framing

---

## Advanced Strategy Framework (Global)

### A. Phase Priorities

1. Early run (Junior to early Classic):
- Build team breadth and spirit-generation cadence.
- Avoid over-racing unless needed for goals/fans.

2. Mid run (Classic):
- Prioritize high-participant Unity training turns.
- Position for strong six-month team races.

3. Senior pre-finals:
- Convert stored Spirit Burst opportunities into high-value stat turns.
- Secure team rank and trainee race readiness simultaneously.

### B. Decision Rules

- High participant Unity turn available: usually highest-value action.
- No good Unity turn and fan gate behind: race.
- No good Unity turn and gates safe: Wit/recovery/setup turns.
- Spirit Burst ready but on low-value facility: delay if safe and fish for better conversion.

### C. Common Failure Modes

- Focusing only on trainee stats while team rank stagnates.
- Entering team checkpoints with poor distance-role distribution.
- Spending burst windows on low-leverage turns.
- Treating Unity Cup like URA and ignoring team-rank training-level dependency.

---

## Planner Integration Recommendations (Repository-Oriented)

1. Team race checkpoint panel:
- Show next six-month team race, target opponent tier, and minimum projected team strength.

2. Team-rank and facility-level mapper:
- Live mapping from team stat ranks to facility levels, with "next threshold" deltas.

3. Spirit Burst scheduler:
- Track ready bursts by member and suggest highest expected-value facility usage windows.

4. Dual-track objective monitor:
- Display trainee mandatory goals and team-rank progression in one view to prevent tunnel vision.

5. Version-aware mechanics flags:
- Toggle JP-only mechanics off by default for Global templates.

---

## Source Matrix

Official/global-facing:
- Umamusume official EN site shell (accessed 2026-03-25)
   - https://umamusume.com/
   - https://umamusume.com/news

Official-announcement mirror (third-party publication of in-game notice text):
- "The new Career scenario 'Unity Cup: Shine On, Team Spirit!' is here!" (updated 2025-11-06)
   - https://umamusume.gg/the-new-career-scenario-unity-cup-shine-on-team-spirit-is-here/

Global mechanics references:
- Game8 Unity Cup (Aoharu Hai) Scenario Guide (updated 2026-03-08)
   - https://game8.co/games/Umamusume-Pretty-Derby/archives/545572
- Game8 Career Scenario Mode Guide (updated 2026-03-16)
   - https://game8.co/games/Umamusume-Pretty-Derby/archives/536350
- GameTora Unity Cup Scenario (updated 2026-01-21)
   - https://gametora.com/umamusume/unity-cup

---

## Confidence and Gaps

- Highest confidence areas: scenario structure, team race cadence, Spirit/Unity core loop, facility-level dependency, and global release timing.
- Moderate confidence areas requiring live-patch vigilance: exact numerical bonuses after balance patches and server-staggered mechanics.
- Known extraction gap: official news listing body on umamusume.com is runtime-loaded; direct static extraction in this environment did not expose article content.

---

## Bottom Line

In Global EN, Unity Cup should be treated as a dual-track optimization scenario: maintain classic trainee-goal compliance while systematically scaling team rank and converting Spirit Burst windows into high-leverage turns. The best runs are those that synchronize both tracks, not those that over-optimize only one.
