<?php

declare(strict_types=1);

/**
 * AI Training Advisory System - Prompt Templates
 *
 * This configuration file contains all prompt templates used by the AI-powered
 * Training Advisory System. These prompts are used with the Neuron AI infrastructure
 * (Ollama + AWS Bedrock) to generate intelligent recommendations for training,
 * skill purchases, and race strategies.
 *
 * **Design Reference**: .kiro/specs/ai-training-advisory/design.md
 * **Requirements**: .kiro/specs/ai-training-advisory/requirements.md
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Training Recommendation Prompt
    |--------------------------------------------------------------------------
    |
    | This prompt is used to generate turn-by-turn training facility recommendations.
    | The AI analyzes character state, support card positions, bond levels, facility
    | levels, energy, mood, and phase-specific goals to recommend optimal training.
    |
    | **Validates: Requirements 3.1**
    |
    */
    'training_recommendation' => [
        'system' => 'You are an expert Uma Musume Pretty Derby training advisor. Your role is to analyze character state and provide optimal training facility recommendations based on game mechanics, support card positions, and career phase goals.',

        'user' => <<<'PROMPT'
Analyze the following training context and provide a training facility recommendation.

**Character State:**
- Turn: {turn_number} / ~72
- Phase: {phase}
- Energy: {energy}/100
- Mood: {mood}

**Current Stats:**
- Speed: {speed}
- Stamina: {stamina}
- Power: {power}
- Guts: {guts}
- Wisdom: {wisdom}

**Support Card Deck:**
{support_cards}

**Facility Levels:**
- Speed: Level {speed_level}
- Stamina: Level {stamina_level}
- Power: Level {power_level}
- Guts: Level {guts_level}
- Wisdom: Level {wisdom_level}

**Upcoming Races:**
{upcoming_races}

**Game Mechanics Context:**
- Friendship Training unlocks at bond ≥80 (provides +10-35% stat multiplier)
- Multi-training bonus: +5% per support card present (max +30%)
- Facility levels increase every 4 uses (max Level 5)
- Energy <50 increases failure rate significantly
- Wisdom training provides +5 energy recovery

**Instructions:**
1. Prioritize Friendship Training if any cards have bond ≥80
2. If energy <40, recommend rest
3. If energy 40-49, recommend Wisdom training for recovery
4. Otherwise, recommend facility with most support cards for multi-training bonus
5. Consider phase-specific stat priorities
6. Provide clear reasoning with expected outcomes

Provide your recommendation in the following format:
- Facility: [Speed/Stamina/Power/Guts/Wisdom/Rest]
- Priority: [Critical/High/Medium/Low]
- Reasoning: [Detailed explanation]
- Expected Outcomes: [Stat gains, bond increases, skill hints]
- Risks: [Any potential issues]
PROMPT,

        'examples' => [
            [
                'input' => 'Turn 25, Energy 80, 3 Speed cards with bond 85+',
                'output' => 'Facility: Speed, Priority: High, Reasoning: Friendship Training available with 3 cards at bond ≥80. Massive stat multiplier (+25-35%). Multi-training bonus +15%.',
            ],
            [
                'input' => 'Turn 15, Energy 35, No friendship training',
                'output' => 'Facility: Rest, Priority: High, Reasoning: Energy critically low at 35/100. High failure rate risk (15-25%). Rest to recover 50-70 energy.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Skill Purchase Advice Prompt
    |--------------------------------------------------------------------------
    |
    | This prompt is used to generate skill purchase recommendations based on
    | available skills, hint levels, SP budget, and character build strategy.
    |
    | **Validates: Requirements 3.2**
    |
    */
    'skill_purchase_advice' => [
        'system' => 'You are an expert Uma Musume Pretty Derby skill advisor. Your role is to analyze available skills and recommend purchases that maximize SP efficiency and race performance.',

        'user' => <<<'PROMPT'
Analyze the following skill purchase context and provide recommendations.

**Character State:**
- Available SP: {sp_available}
- Acquired Skills: {acquired_skills}
- Target Distance: {target_distance}
- Running Style: {running_style}

**Available Skills:**
{available_skills}

**Game Mechanics Context:**
- Hint Level Discounts: Level 1 (10%), Level 2 (20%), Level 3 (30%), Level 4 (35%), Level 5 (40%)
- Gold skills provide significant race performance boosts
- Stamina recovery skills reduce stamina requirements by 150-200
- Typical SP budget per career: 300-500 SP
- Skills can evolve from Normal → Rare variants

**Instructions:**
1. Prioritize gold skills with Level 3+ hints (30%+ discount)
2. Consider SP efficiency (cost after discount vs. impact)
3. Avoid "SP trap" skills with low value or highly conditional activation
4. Recommend waiting for better hints if current options are poor
5. Consider skill evolution paths
6. Provide clear reasoning with SP cost analysis

Provide your recommendation in the following format:
- Skill: [Skill name]
- Priority: [Critical/High/Medium/Low]
- Reasoning: [Why this skill, hint level, SP efficiency]
- SP Cost: [Cost after discount]
- SP Remaining: [Budget after purchase]
- Expected Impact: [Race performance improvement]
PROMPT,

        'examples' => [
            [
                'input' => 'SP: 220, Gold skill "Swinging Maestro" (base 180, hint 3)',
                'output' => 'Skill: Swinging Maestro, Priority: High, Reasoning: Gold stamina recovery skill with Level 3 hint (30% discount). Cost: 126 SP. Reduces stamina requirements by 150-200. Excellent SP efficiency.',
            ],
            [
                'input' => 'SP: 100, Gold skill "Furious Feat" (base 200, hint 1)',
                'output' => 'Recommendation: Wait for better hint. Reasoning: Only 10% discount (cost 180 SP) would leave minimal budget. Wait for Level 3+ hint for better efficiency.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Race Strategy Prompt
    |--------------------------------------------------------------------------
    |
    | This prompt is used to generate pre-race strategy recommendations including
    | running style selection, stamina analysis, and win probability assessment.
    |
    | **Validates: Requirements 3.3**
    |
    */
    'race_strategy' => [
        'system' => 'You are an expert Uma Musume Pretty Derby race strategist. Your role is to analyze character stats, race requirements, and conditions to generate optimal race strategies.',

        'user' => <<<'PROMPT'
Analyze the following race context and provide a comprehensive strategy.

**Character Stats:**
- Speed: {speed}
- Stamina: {stamina}
- Power: {power}
- Guts: {guts}
- Wisdom: {wisdom}

**Aptitude Grades:**
- Distance ({distance}): {distance_aptitude}
- Surface ({surface}): {surface_aptitude}
- Running Styles: Escape ({escape_apt}), Lead ({lead_apt}), Pace ({pace_apt}), Chase ({chase_apt})

**Race Details:**
- Distance: {distance} ({distance_meters}m)
- Surface: {surface}
- Weather: {weather}
- Track Condition: {track_condition}
- Competition Level: {competition_level}

**Equipped Skills:**
{equipped_skills}

**Game Mechanics Context:**
- Stamina Requirements (Escape style):
  * Sprint (1000-1400m): 350-400
  * Mile (1400-1800m): 450-500
  * Medium (1800-2400m): 600-700
  * Long (2400-3600m): 850-1000
- Recovery skills reduce requirements by 150-200 each
- Stats above 1200 count as half effectiveness (soft cap)
- Weather effects: Rainy (-5% Power), Snowy (-15% Power)
- Aptitude grades: S (best), A, B, C, D, E, F, G (worst)

**Instructions:**
1. Recommend optimal running style based on aptitude grades
2. Calculate stamina sufficiency (current vs. required)
3. Assess win probability considering all factors
4. Identify risk factors (stamina deficit, poor aptitudes, weather)
5. Provide preparation advice if character is under-prepared
6. Recommend skills to equip if not already equipped

Provide your strategy in the following format:
- Recommended Running Style: [Escape/Lead/Pace/Chase]
- Stamina Analysis: [Current vs. Required, Sufficient/Deficit]
- Win Probability: [High/Moderate/Low with percentage if possible]
- Risk Factors: [List of concerns]
- Preparation Advice: [What to do before race]
- Recommended Skills: [Skills to equip]
PROMPT,

        'examples' => [
            [
                'input' => 'Speed 850, Stamina 650, Medium race, Escape A-grade',
                'output' => 'Running Style: Escape, Stamina: 650/600 (Sufficient), Win Probability: High (75-85%), Risk Factors: None, Preparation: Character well-prepared.',
            ],
            [
                'input' => 'Speed 600, Stamina 350, Medium race, Escape A-grade',
                'output' => 'Running Style: Escape, Stamina: 350/600 (Deficit -250), Win Probability: Low (20-30%), Risk Factors: Critical stamina deficit, Preparation: Focus next 3 turns on stamina training.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Critical Situation Detection Prompt
    |--------------------------------------------------------------------------
    |
    | This prompt is used to detect critical situations that require immediate
    | attention, such as stamina crises, SP shortages, or energy emergencies.
    |
    | **Validates: Requirements 3.4**
    |
    */
    'critical_detection' => [
        'system' => 'You are an expert Uma Musume Pretty Derby crisis detector. Your role is to identify critical situations that require immediate player attention and provide actionable solutions.',

        'user' => <<<'PROMPT'
Analyze the following character state and identify any critical situations.

**Character State:**
- Turn: {turn_number} / ~72
- Phase: {phase}
- Energy: {energy}/100
- Mood: {mood}
- SP Available: {sp_available}

**Current Stats:**
- Speed: {speed}
- Stamina: {stamina}
- Power: {power}
- Guts: {guts}
- Wisdom: {wisdom}

**Support Card Bonds:**
{support_bonds}

**Facility Levels:**
{facility_levels}

**Upcoming Races:**
{upcoming_races}

**Critical Thresholds:**
- Stamina Crisis: Current stamina < race requirement by 200+
- SP Shortage: Available SP < 100 with important skills unpurchased
- Energy Critical: Energy < 40
- Bond Behind Schedule: Turn 25+ with bonds < 80
- Facility Imbalance: Level variance > 3 between facilities
- Mood Crisis: Mood = Bad or Very Bad

**Instructions:**
1. Check for stamina deficits against upcoming races
2. Assess SP budget vs. remaining career length
3. Evaluate energy level and failure risk
4. Check bond progress toward Friendship Training threshold
5. Identify facility level imbalances
6. Detect poor mood affecting training effectiveness

For each critical situation found, provide:
- Alert Type: [Stamina Crisis/SP Shortage/Energy Critical/etc.]
- Severity: [Critical/High]
- Message: [Clear description of the problem]
- Action Items: [Specific steps to resolve]
- Turns Until Critical: [Urgency indicator]

If no critical situations, respond with: "No critical alerts. Character progression is on track."
PROMPT,

        'examples' => [
            [
                'input' => 'Turn 35, Stamina 320, Medium race Turn 38 (requires 600)',
                'output' => 'Alert: Stamina Crisis, Severity: Critical, Message: Stamina 280 points below requirement, Action: Focus next 3 turns on stamina training, Turns: 3',
            ],
            [
                'input' => 'Turn 50, All stats on track, Energy 75, Bonds 85+',
                'output' => 'No critical alerts. Character progression is on track.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Support Card Deck Analysis Prompt
    |--------------------------------------------------------------------------
    |
    | This prompt is used to analyze support card deck composition, synergy,
    | and bond management strategies.
    |
    | **Validates: Requirements 3.5**
    |
    */
    'deck_analysis' => [
        'system' => 'You are an expert Uma Musume Pretty Derby support card strategist. Your role is to analyze deck composition, identify synergies, and recommend bond management strategies.',

        'user' => <<<'PROMPT'
Analyze the following support card deck and provide recommendations.

**Support Card Deck:**
{support_cards}

**Character Goals:**
- Target Distance: {target_distance}
- Target Running Style: {running_style}
- Current Turn: {turn_number}

**Game Mechanics Context:**
- Friendship Training unlocks at bond ≥80
- Bond increases: Base +7, +9 with Charming trait, +12 with hint mark
- Friendship Training provides +10-35% stat multiplier based on limit breaks
- Multi-training bonus: +5% per card present (max +30%)
- Optimal deck composition varies by target distance and strategy

**Instructions:**
1. Evaluate deck composition for target distance/style
2. Calculate synergy scores between cards
3. Identify missing specializations or redundant cards
4. Recommend training facilities to increase bonds efficiently
5. Estimate turns needed to reach bond 80 threshold
6. Suggest deck changes if composition is suboptimal

Provide your analysis in the following format:
- Deck Synergy Score: [0-100]
- Strengths: [What works well]
- Weaknesses: [What's missing or redundant]
- Bond Management: [Which facilities to prioritize]
- Turns to Friendship Training: [Estimated turns for each card]
- Deck Change Recommendations: [Optional improvements]
PROMPT,

        'examples' => [
            [
                'input' => '3 Speed cards (bonds 70-75), 2 Stamina cards (bonds 65-70), 1 Power card (bond 60), Turn 20',
                'output' => 'Synergy: 75/100, Strengths: Good Speed focus, Weaknesses: Power card underutilized, Bond Management: Prioritize Speed training (5 turns to friendship), then Stamina (8 turns)',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Phase-Specific Goal Tracking Prompt
    |--------------------------------------------------------------------------
    |
    | This prompt is used to track phase-specific milestones and provide
    | readiness assessments for upcoming challenges.
    |
    | **Validates: Requirements 3.6**
    |
    */
    'phase_goal_tracking' => [
        'system' => 'You are an expert Uma Musume Pretty Derby career progression advisor. Your role is to track phase-specific milestones and ensure characters stay on track for A+ grade ratings.',

        'user' => <<<'PROMPT'
Analyze the following character progression and provide phase-specific guidance.

**Current State:**
- Turn: {turn_number} / ~72
- Phase: {phase}

**Current Stats:**
- Speed: {speed}
- Stamina: {stamina}
- Power: {power}
- Guts: {guts}
- Wisdom: {wisdom}

**Phase-Specific Targets:**
{phase_targets}

**Upcoming Events:**
{upcoming_events}

**A+ Grade Requirements (End of Career):**
- Speed: 1200+
- Stamina: Distance-dependent (Sprint 400, Mile 500, Medium 700, Long 1000)
- Power: 800+
- Guts: 600+
- Wisdom: 800+

**Instructions:**
1. Compare current stats to phase-specific targets
2. Identify stats that are behind schedule
3. Provide readiness assessment for upcoming events
4. Recommend corrective actions if behind target
5. Estimate likelihood of achieving A+ grade at current pace

Provide your assessment in the following format:
- Phase Progress: [On Track/Behind/Ahead]
- Stats Behind Target: [List with deficits]
- Upcoming Event Readiness: [Ready/Needs Preparation]
- Corrective Actions: [Specific recommendations]
- A+ Grade Probability: [High/Moderate/Low with percentage]
PROMPT,

        'examples' => [
            [
                'input' => 'Turn 25, Classic Year, Speed 600, Stamina 450, Power 500',
                'output' => 'Progress: On Track, Behind: None, Event Readiness: Ready for Summer Camp, Actions: Continue balanced training, A+ Probability: High (75-85%)',
            ],
            [
                'input' => 'Turn 50, Senior Year, Speed 800, Stamina 400, Power 600',
                'output' => 'Progress: Behind, Behind: Stamina (-200 from target), Event Readiness: Not ready for URA Finals, Actions: Focus stamina training immediately, A+ Probability: Moderate (50-60%)',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Prompt Configuration
    |--------------------------------------------------------------------------
    |
    | General configuration for prompt behavior and formatting.
    |
    */
    'config' => [
        // Maximum tokens for AI responses
        'max_tokens' => 1000,

        // Temperature for creativity (0.0 = deterministic, 1.0 = creative)
        'temperature' => 0.3,

        // Whether to include examples in prompts
        'include_examples' => true,

        // Response format preference
        'response_format' => 'structured', // 'structured' or 'freeform'

        // Timeout for AI requests (seconds)
        'timeout' => 30,
    ],
];
