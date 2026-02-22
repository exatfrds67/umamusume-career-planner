# FLOW-003: Race Strategy System Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.2.0
**Date**: January 28, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Updated with verified game mechanics from Global English Server

---

## 1. Race Preparation & Strategy Analysis Flow

This flow details how `RaceService` and the **Race Strategy Agent** (Neuron AI) analyze upcoming races to provide readiness assessments and strategy recommendations.

```mermaid
flowchart TD
    Start([User Selects Race]) --> FetchDetails[Fetch Race Details]
    FetchDetails --> LoadContext[Load Character Context]
    
    LoadContext --> CalcReadiness[Calculate Readiness Score]
    CalcReadiness --> GenRivals[Generate/Fetch Competitors]
    
    GenRivals --> SimRun[Simulate Race Scenarios]
    SimRun --> CalcWinProb[Calculate Win Probability]
    
    CalcWinProb --> AIAnalysis{AI Analysis Enabled?}
    
    AIAnalysis -->|Yes| TriggerAgent[Trigger RaceStrategyAgent]
    AIAnalysis -->|No| BasicRecs[Generate Basic Recommendations]
    
    TriggerAgent --> AnalyzeStyle[Analyze Running Styles (Nige/Senkou/Sashi/Oikomi)]
    AnalyzeStyle --> CheckSkills[Evaluate Skill Synergies]
    CheckSkills --> RecommendStrategy[Recommend Optimal Strategy]
    
    RecommendStrategy --> DisplayUI[Display Race Preview UI]
    BasicRecs --> DisplayUI
    
    DisplayUI --> UserAction{User Action}
    UserAction -->|Register| SaveRegistration[Save to Plan]
    UserAction -->|Cancel| ReturnToCalendar
```

---

## 2. Race Result Processing Flow

The workflow for recording actual race outcomes during a career run, handling stat updates and scenario objective checks.

```mermaid
flowchart TD
    Start([User Records Result]) --> InputResult[Input Placement & Mood]
    
    InputResult --> CalcRewards[Calculate Rewards]
    CalcRewards -->|Fans| UpdateFans
    CalcRewards -->|Skill Pts| UpdateSP
    CalcRewards -->|Stats| UpdateStats
    
    UpdateFans --> CheckObjectives[Check Scenario Objectives]
    UpdateSP --> CheckObjectives
    UpdateStats --> CheckObjectives
    
    CheckObjectives -->|Objective Met| MarkGoalComplete
    CheckObjectives -->|Objective Failed| FlagRisk
    
    FlagRisk --> AIAdvice[Trigger AI Recovery Advice]
    MarkGoalComplete --> SaveState
    
    SaveState --> UpdateHistory[Update Race History]
    UpdateHistory --> InvalidateCache[Invalidate Predictions]
    
    InvalidateCache --> ReturnDashboard[Return to Dashboard]
```

---

## 3. Win Probability Calculation Flow

The algorithmic logic used by `RaceService` to estimate victory chances based on current character state against generated rivals.

```mermaid
flowchart TD
    Start([Calc Win Probability]) --> GatherInputs[Inputs: Stats, Aptitudes, Skills]
    
    GatherInputs --> RivalCompare[Compare vs Rivals]
    RivalCompare --> CalcStatDelta[Calculate Stat Differentials]
    
    CalcStatDelta --> ApplyAptitude[Apply Aptitude Modifiers]
    ApplyAptitude -->|Distance| DistMod[Distance Aptitude (G-S)]
    ApplyAptitude -->|Surface| SurfMod[Surface Aptitude (G-S)]
    ApplyAptitude -->|Style| StyleMod[Running Style Aptitude (G-S)]
    
    DistMod --> BaseScore
    SurfMod --> BaseScore
    StyleMod --> BaseScore
    
    BaseScore --> ApplySkills[Apply Skill Multipliers]
    ApplySkills -->|Speed Skills| SpdBonus
    ApplySkills -->|Stamina Skills| StaBonus
    ApplySkills -->|Acceleration| AccBonus
    
    SpdBonus --> FinalScore
    StaBonus --> FinalScore
    AccBonus --> FinalScore
    
    FinalScore --> Normalize[Normalize 0-100%]
    Normalize --> ApplyVariance[Apply RNG Variance (+/- 5%)]
    
    ApplyVariance --> ReturnProb[Return Probability]
```

---

## 4. Race Schedule Planning Flow

How the **Race Strategy Agent** assists users in building a race rotation to meet fan count objectives and skill point targets.

```mermaid
flowchart TD
    Start([Plan Schedule]) --> LoadGoals[Load Career Goals]
    LoadGoals --> FetchCalendar[Fetch Race Calendar]
    
    FetchCalendar --> FilterEligible[Filter by Grade/Distance/Surface]
    FilterEligible --> ScoreRaces[Score Races by Value]
    
    ScoreRaces -->|Fan Efficiency| FanScore
    ScoreRaces -->|Skill Pt Efficiency| SPScore
    ScoreRaces -->|Factor Bonus| FactorScore
    
    FanScore --> RankRaces
    SPScore --> RankRaces
    FactorScore --> RankRaces
    
    RankRaces --> DetectConflicts[Detect Turn Conflicts]
    DetectConflicts --> Resolve[Resolve via Priority]
    
    Resolve --> GeneratePlan[Generate Rotation Plan]
    GeneratePlan --> PresentUser[Present to User]
    
    PresentUser --> Confirm[Confirm Schedule]
    Confirm --> SaveSchedule[Persist Race Reservations]
```

---

## 6. Track Condition & Weather Impact Flow

Logic for calculating track condition modifiers based on weather and ground state.

```mermaid
flowchart TD
    Start([Check Track Conditions]) --> GetWeather[Get Current Weather]
    
    GetWeather --> DetermineGround{Ground Condition}
    
    DetermineGround -->|Good| NoMod[No Modifier (100%)]
    DetermineGround -->|Slightly Heavy| SlightMod[Slight Penalty (-2%)]
    DetermineGround -->|Heavy| HeavyMod[Heavy Penalty (-5%)]
    DetermineGround -->|Bad| BadMod[Severe Penalty (-10%)]
    
    NoMod --> CheckAptitude[Check Surface Aptitude]
    SlightMod --> CheckAptitude
    HeavyMod --> CheckAptitude
    BadMod --> CheckAptitude
    
    CheckAptitude --> ApplyAptMod{Aptitude Grade}
    
    ApplyAptMod -->|S| BestMod[Minimal Impact]
    ApplyAptMod -->|A| GoodMod[Low Impact]
    ApplyAptMod -->|B-C| MedMod[Moderate Impact]
    ApplyAptMod -->|D-G| PoorMod[High Impact]
    
    BestMod --> FinalCalc[Calculate Final Performance]
    GoodMod --> FinalCalc
    MedMod --> FinalCalc
    PoorMod --> FinalCalc
```

### 6.1 Track Condition Reference (Game-Accurate)

| Condition | Power Penalty (Turf) | Power Penalty (Dirt) | Speed Penalty | Stamina Drain |
| --- | --- | --- | --- | --- |
| Firm | None | None | None | Normal |
| Good | -50 | -50 | None | Normal |
| Soft | -50 | -100 | None | +2%/sec |
| Heavy | -50 | -100 | -50 | +2%/sec |

### 6.2 Aptitude Grade Scale (Game-Accurate)

- **S**: Maximum grade (best performance, +5% to +10% bonus)
- **A**: Baseline (0% modifier)
- **B**: Good (-10% to -15%)
- **C**: Average (-20% to -25%)
- **D**: Below Average (-30% to -40%)
- **E**: Poor (-50% to -60%)
- **F**: Very Poor (-70% to -80%)
- **G**: Minimum grade (worst performance, -90%)

**Note**: SS grade does not exist in the game. S is the maximum aptitude rating.

### 6.3 Aptitude Modifiers by Category

| Grade | Surface (Power) | Distance (Speed) | Style (Wit) |
| --- | --- | --- | --- |
| S | +5% | +5% | +10% |
| A | 0% | 0% | 0% |
| B | -10% | -10% | -15% |
| C | -20% | -20% | -25% |
| D | -30% | -40% | -40% |
| E | -50% | -60% | -60% |
| F | -70% | -80% | -80% |
| G | -90% | -90% | -90% |

---

## 7. Competitor Generation Flow

Logic for generating realistic rivals for race simulations based on the race grade and current scenario.

```mermaid
flowchart TD
    Start([Generate Rivals]) --> GetRaceGrade{Race Grade}
    
    GetRaceGrade -->|G1| TemplateG1[Load G1 Templates]
    GetRaceGrade -->|G2/G3| TemplateG2[Load G2/G3 Templates]
    GetRaceGrade -->|OP/Pre-OP| TemplateOP[Load OP Templates]
    
    TemplateG1 --> Scaling[Apply Stat Scaling Logic]
    TemplateG2 --> Scaling
    TemplateOP --> Scaling
    
    Scaling --> Randomize[Apply Random Variance]
    Randomize --> AssignStyles[Assign Running Styles]
    
    AssignStyles --> AssignSkills[Equip Competitor Skills]
    AssignSkills --> DetermineUnique[Identify Named Rivals]
    
    DetermineUnique --> ReturnRoster[Return Competitor Roster]
```

---

## Document Control

| Version | Date | Author | Changes |
| --- | --- | --- | --- |
| 2.2.0 | 2026-01-28 | Development Team | Updated with verified game mechanics from Global English Server: Added game-accurate track condition modifiers (Firm/Good/Soft/Heavy with Power/Speed/Stamina penalties), aptitude grade maximum is S (no SS), complete aptitude modifier tables by category (Surface/Distance/Style) |
| 2.1.0 | 2026-01-24 | Development Team | Updated to align with v2.0.0 codebase, Neuron AI agents, and Service layer architecture |
| 1.0.0 | 2026-01-14 | Development Team | Initial flow definitions |

---

## Related Documents

- [PRD-003: Race Strategy](../prds/PRD-003_Race_Strategy.md)
- [SPEC-003: Race Strategy Technical](../specs/SPEC-003_Race_Strategy_Technical.md)
- [010_SCD: Source Code Documentation](../010_SCD_Source_Code_Documentation.md)
