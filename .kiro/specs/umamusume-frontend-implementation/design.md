# Design Document: Umamusume Frontend Implementation

## Overview

This design document specifies the frontend architecture for the Umamusume Pretty Derby Career Planner web application. The system provides a comprehensive, accessible, and performant user interface built on Laravel 12 with Blade templating, Tailwind CSS v4, Alpine.js, and Progressive Web App (PWA) capabilities.

The frontend implements a component-based architecture with reusable UI elements, responsive design patterns, accessibility features compliant with WCAG 2.2 AA standards, and offline-first capabilities through service workers.

## Architecture

### Technology Stack

**Core Technologies:**

- Laravel 12 with Blade templating engine for server-side rendering
- Tailwind CSS v4 for utility-first styling with CSS-first configuration
- Alpine.js for reactive JavaScript components
- Vite for asset bundling and hot module replacement

**Progressive Web App:**

- Service Worker for offline caching and background sync
- Web App Manifest for installability
- IndexedDB for client-side data storage
- Push Notifications API for real-time updates

**Accessibility:**

- ARIA attributes for semantic markup
- Focus management for keyboard navigation
- Screen reader announcements via live regions
- High contrast mode and text scaling support

**Performance:**

- Lazy loading for images and components
- Code splitting for route-based chunks
- Asset optimization with WebP and responsive images
- CSS and JavaScript minification

### Application Structure

```
resources/
├── views/
│   ├── layouts/
│   │   ├── app.blade.php           # Main application layout
│   │   ├── guest.blade.php         # Guest/auth layout
│   │   └── components/             # Blade components
│   ├── dashboard/
│   │   ├── index.blade.php         # Dashboard overview
│   │   └── components/             # Dashboard-specific components
│   ├── character/
│   │   ├── create.blade.php        # Character creation wizard
│   │   ├── show.blade.php          # Character detail view
│   │   └── components/             # Character components
│   ├── training/
│   │   ├── index.blade.php         # Training selection
│   │   └── components/             # Training components
│   ├── race/
│   │   ├── index.blade.php         # Race calendar
│   │   ├── prepare.blade.php       # Race preparation
│   │   └── components/             # Race components
│   ├── skill/
│   │   ├── index.blade.php         # Skill management
│   │   └── components/             # Skill components
│   └── support/
│       ├── index.blade.php         # Support card deck
│       └── components/             # Support card components
├── css/
│   └── app.css                     # Tailwind CSS with custom theme
└── js/
    ├── app.js                      # Application entry point
    ├── bootstrap.js                # Laravel Echo and Axios setup
    ├── core/
    │   ├── EventBus.js             # Global event bus
    │   └── PWAManager.js           # PWA lifecycle management
    └── components/
        ├── CharacterAvatar.js      # Avatar component
        ├── BackgroundSystem.js     # Theme and background management
        ├── AssetOptimization.js    # Image lazy loading
        ├── DesignSystem.js         # Component library
        ├── ResponsiveSystem.js     # Breakpoint management
        ├── AccessibilitySystem.js  # A11y features
        └── AccessibilitySettings.js # A11y settings panel
```

## Components and Interfaces

### 1. Layout System

**Main Application Layout (`layouts/app.blade.php`):**

- Header with navigation, notifications, and user menu
- Sidebar navigation for main sections
- Main content area with breadcrumbs
- Footer with accessibility controls
- Skip links for keyboard navigation

**Responsive Behavior:**

- Desktop (≥1024px): Sidebar + main content
- Tablet (768px-1023px): Collapsible sidebar
- Mobile (<768px): Bottom navigation bar

### 2. Dashboard Components

**Dashboard Overview:**

- Character status card (stats, mood, energy)
- Goals progress panel with progress bars
- Upcoming races list (next 3 races)
- Training suggestions with AI recommendations
- Support deck preview (6 cards)
- Recent activity timeline

**Component Interfaces:**

```javascript
// Character Status Card
{
  character: {
    id: number,
    name: string,
    avatar: string,
    stats: { speed, stamina, power, guts, wisdom },
    mood: string,
    energy: number,
    turn: number
  }
}

// Goals Progress Panel
{
  goals: [{
    id: number,
    name: string,
    target: number,
    current: number,
    progress: number,
    status: 'on_track' | 'at_risk' | 'off_track'
  }]
}
```

### 3. Character Creation Wizard

**Step 1: Trainee Selection**

- Search and filter interface
- Trainee card grid with rarity badges
- Preview panel with base stats
- Scenario dropdown selection

**Step 2: Parent Selection**

- Two-column parent picker
- Inheritance preview calculator
- Stat factor display
- Growth rate visualization

**Step 3: Support Deck Configuration**

- 6-slot card grid
- Drag-and-drop card assignment
- Synergy score calculator
- Auto-fill suggestions

**Step 4: Review and Confirmation**

- Summary of all selections
- Projected starting stats
- Goal recommendations
- Create button with validation

**Wizard State Management:**

```javascript
{
  currentStep: number,
  steps: ['trainee', 'parents', 'deck', 'review'],
  data: {
    trainee: { id, name, scenario },
    parents: [{ id, name }, { id, name }],
    deck: [{ slot, cardId }],
    goals: [{ type, target }]
  },
  validation: {
    trainee: boolean,
    parents: boolean,
    deck: boolean
  }
}
```

### 4. Training Selection Interface

**Training Options Display:**

- Grid/list of 5 training facilities + race option
- Predicted stat gains for each option
- Risk assessment badges (low/medium/high)
- Support card participation indicators
- Skill hint availability markers
- AI recommendation highlight

**Training Card Structure:**

```javascript
{
  facility: 'speed' | 'stamina' | 'power' | 'guts' | 'wisdom' | 'race',
  gains: {
    primary: number,
    secondary: { stat: string, value: number }[]
  },
  risk: number,
  skillHints: [{ name: string, chance: number }],
  supportCards: [{ id: number, name: string, bonus: number }],
  moodImpact: number,
  energyCost: number,
  aiRecommended: boolean,
  aiReasoning: string
}
```

**Confirmation Modal:**

- Selected training summary
- Final stat gain predictions
- Risk warnings (if applicable)
- Mood/energy impact
- Confirm/Cancel buttons

### 5. Race Preparation Interface

**Race Analysis Panel:**

- Race details (grade, distance, surface, track)
- Stat requirements with readiness indicators
- Running style recommendations with scores
- Skill recommendations (essential/recommended)
- Performance forecast with placement probability

**Strategy Selection:**

- Multiple strategy cards with success rates
- AI-recommended strategy highlight
- Strategy reasoning and risk assessment
- Stat gap analysis with training suggestions

**Race Execution:**

- Race animation (2-3 minutes, skippable)
- Real-time position updates
- Final results with grade achieved
- Rewards display (fans, grade points, items)

### 6. Skill Management Interface

**Skill Inventory:**

- Owned skills list with evolution status
- Available skills grid with filters
- SP balance display
- Skill categories (speed, stamina, power, etc.)

**Skill Acquisition Flow:**

```javascript
{
  skill: {
    id: number,
    name: string,
    rarity: 'normal' | 'rare' | 'unique',
    baseCost: number,
    hints: number,
    finalCost: number,
    effect: string,
    evolution: {
      available: boolean,
      target: string,
      cost: number,
      benefit: string
    }
  }
}
```

**Skill Filters:**

- Owned/Available/All toggle
- Rarity filter (normal/rare/unique)
- Category filter (by stat type)
- Sort options (cost/name/rarity)

### 7. Support Card Deck Builder

**Deck Configuration:**

- 6-card grid with drag-and-drop
- Card collection browser
- Bond level indicators
- Synergy score calculator
- Meta tier rankings

**Card Details Panel:**

```javascript
{
  card: {
    id: number,
    name: string,
    rarity: 'SSR' | 'SR' | 'R',
    type: 'speed' | 'stamina' | 'power' | 'guts' | 'wisdom' | 'friend',
    limitBreak: number,
    bondLevel: number,
    skills: [{ name: string, type: string }],
    bonuses: { stat: string, value: number }[],
    metaTier: 'S' | 'A' | 'B' | 'C'
  }
}
```

**Synergy Calculation:**

- Type distribution score
- Bond level average
- Meta card count
- Skill coverage analysis
- Overall synergy rating (0-100)

## Data Models

### Frontend State Management

**Character State:**

```typescript
interface CharacterState {
  id: number;
  name: string;
  avatar: string;
  turn: number;
  scenario: 'ura_finale' | 'unity_cup';
  stats: {
    speed: number;
    stamina: number;
    power: number;
    guts: number;
    wisdom: number;
  };
  grades: {
    speed: Grade;
    stamina: Grade;
    power: Grade;
    guts: Grade;
    wisdom: Grade;
  };
  aptitudes: {
    distance: { [key: string]: Grade };
    surface: { [key: string]: Grade };
    runningStyle: { [key: string]: Grade };
  };
  mood: 'excellent' | 'good' | 'normal' | 'bad' | 'terrible';
  energy: number;
  motivation: number;
  fans: number;
  skillPoints: number;
}

type Grade = 'SS' | 'S' | 'A' | 'B' | 'C' | 'D' | 'E' | 'F' | 'G';
```

**Training State:**

```typescript
interface TrainingState {
  turn: number;
  availableOptions: TrainingOption[];
  history: TrainingHistory[];
  aiRecommendation: {
    facility: string;
    reasoning: string;
    score: number;
  } | null;
}

interface TrainingOption {
  facility: string;
  predictions: StatGains;
  risk: number;
  skillHints: SkillHint[];
  supportCards: SupportCardBonus[];
  moodImpact: number;
  energyCost: number;
}
```

**Race State:**

```typescript
interface RaceState {
  upcoming: Race[];
  history: RaceResult[];
  currentPreparation: {
    race: Race;
    readiness: ReadinessAssessment;
    strategies: Strategy[];
    recommendations: Recommendation[];
  } | null;
}

interface Race {
  id: number;
  name: string;
  grade: 'G1' | 'G2' | 'G3' | 'OP' | 'Pre-OP';
  distance: number;
  surface: 'turf' | 'dirt';
  track: string;
  turn: number;
  requirements: StatRequirements;
}
```

**Skill State:**

```typescript
interface SkillState {
  owned: Skill[];
  available: Skill[];
  sp: number;
  hints: { [skillId: number]: number };
  filters: {
    ownership: 'owned' | 'available' | 'all';
    rarity: ('normal' | 'rare' | 'unique')[];
    category: string[];
  };
  sort: 'cost' | 'name' | 'rarity';
}
```

**Support Deck State:**

```typescript
interface SupportDeckState {
  active: (SupportCard | null)[];  // 6 slots
  collection: SupportCard[];
  synergy: {
    score: number;
    distribution: { [type: string]: number };
    bondAverage: number;
    metaCount: number;
  };
  filters: {
    rarity: ('SSR' | 'SR' | 'R')[];
    type: string[];
    bondLevel: number;
  };
}
```

### Local Storage Schema

**User Preferences:**

```javascript
{
  theme: 'light' | 'dark' | 'auto',
  accessibility: {
    textSize: number,        // 80-200
    highContrast: boolean,
    reducedMotion: boolean,
    keyboardNav: boolean
  },
  ui: {
    sidebarCollapsed: boolean,
    gridView: boolean,
    compactMode: boolean
  }
}
```

**PWA Cache:**

```javascript
{
  version: string,
  lastSync: timestamp,
  pendingActions: [{
    type: string,
    data: object,
    timestamp: number
  }],
  offlineData: {
    characters: Character[],
    supportCards: SupportCard[],
    skills: Skill[]
  }
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Navigation State Consistency

*For any* navigation action, the active navigation state should match the currently displayed content section, and the URL should reflect the current route.

**Validates: Requirements 1.2**

### Property 2: Responsive Layout Adaptation

*For any* viewport size change, the layout should adapt to the appropriate breakpoint (mobile/tablet/desktop) and all interactive elements should remain accessible.

**Validates: Requirements 1.3, 9.2**

### Property 3: Keyboard Navigation Focus

*For any* keyboard navigation action, focus indicators should be visible with sufficient contrast (3:1 minimum) and focus should move in logical order.

**Validates: Requirements 1.4, 10.1**

### Property 4: Hover State Feedback

*For any* interactive element, hovering should apply visual feedback through CSS class changes with smooth transitions.

**Validates: Requirements 1.5**

### Property 5: Trainee Selection Display

*For any* trainee selection, all required details (rarity, base stats, aptitudes) should be displayed in the preview panel.

**Validates: Requirements 2.2**

### Property 6: Inheritance Calculation

*For any* parent pair selection, the inheritance preview should calculate and display stat factors and growth rates based on parent genetics.

**Validates: Requirements 2.3**

### Property 7: Goal Validation

*For any* goal configuration, the system should validate feasibility and display appropriate warnings or recommendations.

**Validates: Requirements 2.4**

### Property 8: Wizard State Persistence

*For any* backward navigation in the wizard, previously entered data should persist without loss.

**Validates: Requirements 2.6**

### Property 9: Training Options Display

*For any* training state, all available facilities should be displayed with predicted stat gains, risk assessment, and support card bonuses.

**Validates: Requirements 3.1**

### Property 10: AI Recommendation Highlighting

*For any* training state with AI recommendations, the recommended option should be visually highlighted with reasoning and efficiency score displayed.

**Validates: Requirements 3.2**

### Property 11: Training Selection Details

*For any* training selection, skill hints, support card bonuses, and mood predictions should be displayed before confirmation.

**Validates: Requirements 3.3**

### Property 12: Training Impact Warnings

*For any* training that negatively affects mood or energy, warnings should be displayed before user confirmation.

**Validates: Requirements 3.6**

### Property 13: Race Details Display

*For any* race view, all race requirements, track details, and readiness assessment should be displayed.

**Validates: Requirements 4.1**

### Property 14: Race Strategy Options

*For any* race analysis, multiple strategy options with success probabilities should be provided.

**Validates: Requirements 4.2**

### Property 15: Stat Gap Recommendations

*For any* race with stat gaps, training recommendations to close gaps should be displayed.

**Validates: Requirements 4.3**

### Property 16: Skill Inventory Display

*For any* skill state, owned skills, available skills, and SP balance should be displayed correctly.

**Validates: Requirements 5.1**

### Property 17: Skill Cost Calculation

*For any* skill selection, the cost calculation should include hint reductions and display the final cost.

**Validates: Requirements 5.2**

### Property 18: Skill Evolution Offering

*For any* skill with available evolution, evolution options with cost and benefit comparison should be offered.

**Validates: Requirements 5.3**

### Property 19: Skill Filtering

*For any* filter combination (rarity, category, cost), the skill list should update to show only matching skills.

**Validates: Requirements 5.5**

### Property 20: Hint Cost Reduction

*For any* skill with collected hints, the acquisition cost should be automatically reduced by the hint discount.

**Validates: Requirements 5.6**

### Property 21: Support Deck Display

*For any* support deck state, all 6 card slots should display with bond levels and specializations.

**Validates: Requirements 6.1**

### Property 22: Deck Drag-and-Drop

*For any* card swap via drag-and-drop, the deck should update and synergy should recalculate in real-time.

**Validates: Requirements 6.2**

### Property 23: Synergy Recalculation

*For any* deck composition change, the synergy score should recalculate and optimization suggestions should update.

**Validates: Requirements 6.3**

### Property 24: Card Details Display

*For any* support card selection, skill provisions, bond bonuses, and meta tier ranking should be displayed.

**Validates: Requirements 6.4**

### Property 25: Support Card Hints

*For any* support card in the deck, skill hints provided by that card should be displayed.

**Validates: Requirements 6.5**

### Property 26: Bond Level Progression

*For any* bond level increase, additional bonuses should unlock and card effectiveness should update.

**Validates: Requirements 6.6**

### Property 27: Stat Visualization

*For any* character state, stat bars should display current values, grades, and target goals.

**Validates: Requirements 7.1**

### Property 28: Stat Animation

*For any* stat change, stat bars should animate the transition smoothly from old to new value.

**Validates: Requirements 7.2**

### Property 29: Goal Progress Display

*For any* goal configuration, progress bars should display with accurate percentage completion.

**Validates: Requirements 7.3**

### Property 30: Stat Gap Highlighting

*For any* stat below target, deficiency warnings should be highlighted with appropriate indicators.

**Validates: Requirements 7.4**

### Property 31: Grade Badge Display

*For any* aptitude display, the correct grade badge (SS/S/A/B/C/D/E/F/G) with appropriate color should be shown.

**Validates: Requirements 7.6**

### Property 32: Form Validation Errors

*For any* invalid form submission, field-specific error messages should be displayed for each invalid field.

**Validates: Requirements 8.1**

### Property 33: Error Focus and Announcement

*For any* validation error, focus should move to the first invalid field and errors should be announced to screen readers.

**Validates: Requirements 8.2**

### Property 34: Real-time Error Removal

*For any* error correction, error messages should be removed in real-time as the user fixes the issue.

**Validates: Requirements 8.3**

### Property 35: Server Error Display

*For any* server error, user-friendly error messages with recovery options should be displayed.

**Validates: Requirements 8.4**

### Property 36: Form Submission Prevention

*For any* form with validation errors, submission should be prevented until all errors are resolved.

**Validates: Requirements 8.5**

### Property 37: Form Loading States

*For any* form submission, loading states should be displayed and submit buttons should be disabled.

**Validates: Requirements 8.6**

### Property 38: Mobile Touch Optimization

*For any* interactive element on mobile, touch targets should be minimum 44x44px.

**Validates: Requirements 9.3**

### Property 39: Responsive Image Sources

*For any* image, the appropriate source should be loaded based on device resolution and viewport size.

**Validates: Requirements 9.4**

### Property 40: Orientation Layout Adjustment

*For any* orientation change, the layout should adjust appropriately for the new orientation.

**Validates: Requirements 9.6**

### Property 41: ARIA Labels

*For any* interactive element, descriptive ARIA labels should be provided for screen readers.

**Validates: Requirements 10.2**

### Property 42: Non-Color Indicators

*For any* information conveyed by color, additional non-color indicators (icons, text, patterns) should be provided.

**Validates: Requirements 10.3**

### Property 43: Text Contrast Ratios

*For any* text element, contrast ratio should meet WCAG 2.2 AA standards (4.5:1 for normal, 3:1 for large text).

**Validates: Requirements 10.4**

### Property 44: Text Resizing Support

*For any* text size adjustment up to 200%, functionality should remain intact without horizontal scrolling.

**Validates: Requirements 10.5**

### Property 45: Keyboard Accessibility

*For any* interactive element, keyboard shortcuts and tab navigation should be supported.

**Validates: Requirements 10.6**

### Property 46: Offline Indicator

*For any* offline event, an offline indicator should be displayed and offline mode should be enabled.

**Validates: Requirements 11.2**

### Property 47: Connection Restoration Sync

*For any* connection restoration, pending data should be automatically synced.

**Validates: Requirements 11.3**

### Property 48: Update Notifications

*For any* available update, the user should be notified with an option to update.

**Validates: Requirements 11.4**

### Property 49: Offline Action Queuing

*For any* action performed offline, the action should be queued for synchronization when online.

**Validates: Requirements 11.6**

### Property 50: Image Lazy Loading

*For any* image, lazy loading with blur-up placeholders should be used.

**Validates: Requirements 12.1**

### Property 51: Reduced Motion Respect

*For any* user with reduced motion preference, non-essential animations should be disabled.

**Validates: Requirements 12.4**

### Property 52: Responsive Background Images

*For any* background image, the appropriate image should be loaded based on device size and theme.

**Validates: Requirements 12.6**

### Property 53: Theme Toggle

*For any* theme toggle action, the theme should switch between light and dark modes with smooth transitions.

**Validates: Requirements 13.1**

### Property 54: System Theme Adaptation

*For any* system theme preference change, the application should automatically adapt to match.

**Validates: Requirements 13.2**

### Property 55: Theme Persistence

*For any* theme change, the preference should be persisted in local storage and restored on next visit.

**Validates: Requirements 13.3**

### Property 56: Dark Mode Styling

*For any* dark mode activation, appropriate background images and color schemes should be applied.

**Validates: Requirements 13.4**

### Property 57: Theme Contrast Maintenance

*For any* theme (light or dark), WCAG 2.2 AA contrast ratios should be maintained.

**Validates: Requirements 13.5**

### Property 58: Theme Component Consistency

*For any* theme change, all components should update consistently across the application.

**Validates: Requirements 13.6**

### Property 59: Component Styling Consistency

*For any* component usage, consistent styling from the design system should be applied.

**Validates: Requirements 14.2**

### Property 60: Form Component Standardization

*For any* form, standardized form components with validation should be used.

**Validates: Requirements 14.4**

### Property 61: Toast Auto-Dismiss

*For any* alert display, toast notifications with auto-dismiss should be used.

**Validates: Requirements 14.6**

### Property 62: Avatar Lazy Loading

*For any* character avatar, optimized images with lazy loading should be used.

**Validates: Requirements 15.1**

### Property 63: Avatar Size Variants

*For any* avatar display, the appropriate size variant (sm/md/lg/xl) should be used.

**Validates: Requirements 15.2**

### Property 64: Avatar Hover Effects

*For any* interactive avatar, hover effects and transitions should be provided.

**Validates: Requirements 15.4**

### Property 65: Avatar Progressive Loading

*For any* avatar load, blur-up technique should be used for progressive loading.

**Validates: Requirements 15.6**

### Property 66: WebSocket Updates

*For any* race result availability, updates should be pushed to the client via WebSocket.

**Validates: Requirements 16.2**

### Property 67: WebSocket Reconnection

*For any* connection loss, reconnection should be attempted with exponential backoff.

**Validates: Requirements 16.4**

### Property 68: Update Announcements

*For any* update arrival, changes should be announced to screen readers via ARIA live regions.

**Validates: Requirements 16.6**

### Property 69: Real-time Search Filtering

*For any* search term entry, results should filter in real-time without page reload.

**Validates: Requirements 17.1**

### Property 70: Filter Application

*For any* filter application, results should update without page reload.

**Validates: Requirements 17.2**

### Property 71: Search Clear Restoration

*For any* search clear action, the full result set should be restored.

**Validates: Requirements 17.4**

### Property 72: Active Filter Display

*For any* active filter, filter badges with clear options should be displayed.

**Validates: Requirements 17.6**

### Property 73: Element Fade-in

*For any* UI element appearance, fade-in animations should be used.

**Validates: Requirements 18.1**

### Property 74: Stat Bar Animation

*For any* stat bar update, value changes should be animated smoothly.

**Validates: Requirements 18.2**

### Property 75: Modal Transitions

*For any* modal opening, scale and fade transitions should be used.

**Validates: Requirements 18.3**

### Property 76: Page Loading States

*For any* page transition, appropriate loading states should be displayed.

**Validates: Requirements 18.4**

### Property 77: Reduced Motion Animation Respect

*For any* user with reduced motion preference, the system should respect this preference for all animations.

**Validates: Requirements 18.5**

### Property 78: Text Size Scaling

*For any* text size adjustment, text should scale from 80% to 200% as configured.

**Validates: Requirements 19.2**

### Property 79: High Contrast Mode

*For any* high contrast enablement, border widths and contrast ratios should increase.

**Validates: Requirements 19.3**

### Property 80: Keyboard Navigation Enhancement

*For any* keyboard navigation enablement, focus indicators should be enhanced.

**Validates: Requirements 19.4**

### Property 81: Accessibility Preference Persistence

*For any* accessibility setting change, preferences should be persisted in local storage.

**Validates: Requirements 19.5**

### Property 82: Immediate Setting Application

*For any* accessibility setting change, changes should apply immediately without page reload.

**Validates: Requirements 19.6**

### Property 83: Error Boundary Display

*For any* JavaScript error, an error boundary with recovery options should be displayed.

**Validates: Requirements 20.1**

### Property 84: API Error Messages

*For any* API call failure, user-friendly error messages should be displayed.

**Validates: Requirements 20.2**

### Property 85: Network Error Retry

*For any* network error, retry functionality should be offered.

**Validates: Requirements 20.3**

### Property 86: Error Logging and Fallback

*For any* critical error, errors should be logged for debugging while showing fallback UI.

**Validates: Requirements 20.4**

### Property 87: Error Cascade Prevention

*For any* error occurrence, error cascades that break the entire application should be prevented.

**Validates: Requirements 20.5**

### Property 88: Error Recovery Continuation

*For any* error resolution, users should be able to continue without full page reload.

**Validates: Requirements 20.6**

## Error Handling

### Error Boundary Strategy

**JavaScript Error Boundaries:**

- Wrap major sections in error boundaries
- Display fallback UI with recovery options
- Log errors to monitoring service
- Prevent error cascades

**Error Boundary Levels:**

1. **Application Level**: Catches catastrophic errors, shows full-page fallback
2. **Route Level**: Catches route-specific errors, shows section fallback
3. **Component Level**: Catches component errors, shows component fallback

**Error Recovery Options:**

- Retry action button
- Navigate to safe route (dashboard)
- Reload page button
- Report error button

### API Error Handling

**Error Response Structure:**

```javascript
{
  error: {
    code: string,
    message: string,
    details: object,
    recoverable: boolean
  }
}
```

**Error Types and Handling:**

- **400 Bad Request**: Display validation errors inline
- **401 Unauthorized**: Redirect to login with return URL
- **403 Forbidden**: Show permission denied message
- **404 Not Found**: Show not found page with navigation
- **422 Unprocessable Entity**: Display field-specific validation errors
- **500 Server Error**: Show generic error with retry option
- **503 Service Unavailable**: Show maintenance message

### Network Error Handling

**Offline Detection:**

- Monitor `navigator.onLine` status
- Display offline indicator banner
- Queue actions for later sync
- Enable offline mode features

**Connection Recovery:**

- Automatic reconnection attempts
- Exponential backoff strategy
- Sync queued actions on reconnection
- Notify user of sync status

### Validation Error Handling

**Client-Side Validation:**

- Real-time validation on blur
- Inline error messages
- Field-level error styling
- Form-level error summary

**Server-Side Validation:**

- Display server validation errors
- Map errors to form fields
- Preserve user input
- Focus first error field

### User-Friendly Error Messages

**Error Message Guidelines:**

- Use plain language, avoid technical jargon
- Explain what went wrong
- Provide actionable next steps
- Include recovery options

**Example Error Messages:**

```
❌ "Unable to save character"
✅ "We couldn't save your character. Please check your internet connection and try again."

❌ "API request failed with status 500"
✅ "Something went wrong on our end. We're working to fix it. Please try again in a few minutes."

❌ "Validation error: field required"
✅ "Please enter a character name to continue."
```

## Testing Strategy

### Dual Testing Approach

The frontend will be tested using both **unit tests** and **property-based tests** to ensure comprehensive coverage:

**Unit Tests:**

- Test specific examples and edge cases
- Verify component rendering with known inputs
- Test user interaction flows
- Validate error handling scenarios
- Test integration between components

**Property-Based Tests:**

- Verify universal properties across all inputs
- Test component behavior with random data
- Validate accessibility properties
- Test responsive behavior at various breakpoints
- Verify state management consistency

### Testing Framework

**Primary Framework: Vitest + Testing Library**

- Vitest for fast unit testing
- @testing-library/vue or @testing-library/react for component testing
- @testing-library/user-event for user interaction simulation
- @testing-library/jest-dom for DOM assertions

**Property-Based Testing: fast-check**

- Generate random test data
- Test properties across input space
- Shrink failing cases to minimal examples
- Minimum 100 iterations per property test

### Unit Test Coverage

**Component Tests:**

```javascript
// Example: Character Avatar Component
describe('CharacterAvatar', () => {
  it('renders with correct image source', () => {
    const { getByRole } = render(CharacterAvatar, {
      props: { character: { id: 1, name: 'Test', avatar: 'test.jpg' } }
    });
    expect(getByRole('img')).toHaveAttribute('src', expect.stringContaining('test.jpg'));
  });

  it('displays fallback on image error', () => {
    const { getByRole } = render(CharacterAvatar, {
      props: { character: { id: 1, name: 'Test', avatar: 'invalid.jpg' } }
    });
    fireEvent.error(getByRole('img'));
    expect(getByRole('img')).toHaveAttribute('src', expect.stringContaining('default'));
  });

  it('applies correct size class', () => {
    const { container } = render(CharacterAvatar, {
      props: { character: { id: 1, name: 'Test' }, size: 'lg' }
    });
    expect(container.firstChild).toHaveClass('character-avatar-lg');
  });
});
```

**Integration Tests:**

```javascript
// Example: Character Creation Wizard
describe('CharacterCreationWizard', () => {
  it('completes full wizard flow', async () => {
    const { getByText, getByLabelText } = render(CharacterCreationWizard);
    
    // Step 1: Select trainee
    await userEvent.click(getByText('Tokai Teio'));
    await userEvent.click(getByText('Next'));
    
    // Step 2: Select parents
    await userEvent.click(getByText('Parent 1'));
    await userEvent.click(getByText('Parent 2'));
    await userEvent.click(getByText('Next'));
    
    // Step 3: Configure deck
    // ... deck configuration
    await userEvent.click(getByText('Next'));
    
    // Step 4: Review and create
    await userEvent.click(getByText('Create Character'));
    
    expect(getByText('Character created successfully')).toBeInTheDocument();
  });
});
```

### Property-Based Test Coverage

**Property Test Configuration:**

```javascript
import fc from 'fast-check';

// Configure property tests to run 100 iterations minimum
const propertyConfig = {
  numRuns: 100,
  verbose: true
};
```

**Example Property Tests:**

**Property 1: Navigation State Consistency**

```javascript
// Feature: umamusume-frontend-implementation, Property 1: Navigation State Consistency
test('navigation state matches displayed content', () => {
  fc.assert(
    fc.property(
      fc.constantFrom('dashboard', 'character', 'training', 'race', 'skill', 'support'),
      (section) => {
        const { getByRole, getByText } = render(App);
        const navLink = getByRole('link', { name: new RegExp(section, 'i') });
        
        fireEvent.click(navLink);
        
        // Active state should match
        expect(navLink).toHaveClass('nav-link-active');
        // Content should be displayed
        expect(getByText(new RegExp(section, 'i'))).toBeInTheDocument();
        // URL should reflect route
        expect(window.location.pathname).toContain(section);
      }
    ),
    propertyConfig
  );
});
```

**Property 2: Responsive Layout Adaptation**

```javascript
// Feature: umamusume-frontend-implementation, Property 2: Responsive Layout Adaptation
test('layout adapts to viewport size', () => {
  fc.assert(
    fc.property(
      fc.integer({ min: 320, max: 1920 }),
      (width) => {
        window.innerWidth = width;
        window.dispatchEvent(new Event('resize'));
        
        const { container } = render(App);
        const breakpoint = width < 768 ? 'mobile' : width < 1024 ? 'tablet' : 'desktop';
        
        expect(document.body).toHaveAttribute('data-breakpoint', breakpoint);
        
        // All interactive elements should be accessible
        const interactiveElements = container.querySelectorAll('button, a, input, select, textarea');
        interactiveElements.forEach(el => {
          const rect = el.getBoundingClientRect();
          expect(rect.width).toBeGreaterThan(0);
          expect(rect.height).toBeGreaterThan(0);
        });
      }
    ),
    propertyConfig
  );
});
```

**Property 8: Wizard State Persistence**

```javascript
// Feature: umamusume-frontend-implementation, Property 8: Wizard State Persistence
test('wizard preserves data on backward navigation', () => {
  fc.assert(
    fc.property(
      fc.record({
        trainee: fc.integer({ min: 1, max: 50 }),
        parent1: fc.integer({ min: 1, max: 100 }),
        parent2: fc.integer({ min: 1, max: 100 }),
        scenario: fc.constantFrom('ura_finale', 'unity_cup')
      }),
      (wizardData) => {
        const { getByText, getByLabelText } = render(CharacterCreationWizard);
        
        // Fill step 1
        selectTrainee(wizardData.trainee);
        selectScenario(wizardData.scenario);
        clickNext();
        
        // Fill step 2
        selectParent(1, wizardData.parent1);
        selectParent(2, wizardData.parent2);
        clickNext();
        
        // Go back to step 1
        clickBack();
        clickBack();
        
        // Data should persist
        expect(getSelectedTrainee()).toBe(wizardData.trainee);
        expect(getSelectedScenario()).toBe(wizardData.scenario);
      }
    ),
    propertyConfig
  );
});
```

**Property 17: Skill Cost Calculation**

```javascript
// Feature: umamusume-frontend-implementation, Property 17: Skill Cost Calculation
test('skill cost includes hint reductions', () => {
  fc.assert(
    fc.property(
      fc.record({
        baseCost: fc.integer({ min: 80, max: 200 }),
        hints: fc.integer({ min: 0, max: 5 })
      }),
      ({ baseCost, hints }) => {
        const { getByText } = render(SkillManagement, {
          props: {
            skill: { id: 1, name: 'Test Skill', baseCost, hints }
          }
        });
        
        const hintDiscount = hints * (baseCost * 0.2);
        const expectedCost = baseCost - hintDiscount;
        
        expect(getByText(new RegExp(`${expectedCost} SP`))).toBeInTheDocument();
      }
    ),
    propertyConfig
  );
});
```

**Property 43: Text Contrast Ratios**

```javascript
// Feature: umamusume-frontend-implementation, Property 43: Text Contrast Ratios
test('text maintains WCAG AA contrast ratios', () => {
  fc.assert(
    fc.property(
      fc.constantFrom('light', 'dark'),
      (theme) => {
        document.body.setAttribute('data-theme', theme);
        const { container } = render(App);
        
        const textElements = container.querySelectorAll('p, span, h1, h2, h3, h4, h5, h6, label, button');
        
        textElements.forEach(el => {
          const styles = window.getComputedStyle(el);
          const fontSize = parseFloat(styles.fontSize);
          const isLargeText = fontSize >= 18 || (fontSize >= 14 && styles.fontWeight >= 700);
          
          const contrast = calculateContrast(
            styles.color,
            styles.backgroundColor
          );
          
          const minContrast = isLargeText ? 3 : 4.5;
          expect(contrast).toBeGreaterThanOrEqual(minContrast);
        });
      }
    ),
    propertyConfig
  );
});
```

**Property 55: Theme Persistence**

```javascript
// Feature: umamusume-frontend-implementation, Property 55: Theme Persistence
test('theme preference persists across sessions', () => {
  fc.assert(
    fc.property(
      fc.constantFrom('light', 'dark', 'auto'),
      (theme) => {
        // Set theme
        const { getByRole } = render(App);
        const themeToggle = getByRole('button', { name: /theme/i });
        
        // Change to target theme
        while (getCurrentTheme() !== theme) {
          fireEvent.click(themeToggle);
        }
        
        // Verify localStorage
        expect(localStorage.getItem('theme')).toBe(theme);
        
        // Simulate page reload
        const { getByRole: getByRoleAfterReload } = render(App);
        
        // Theme should be restored
        expect(getCurrentTheme()).toBe(theme);
      }
    ),
    propertyConfig
  );
});
```

### Accessibility Testing

**Automated Accessibility Tests:**

```javascript
import { axe, toHaveNoViolations } from 'jest-axe';

expect.extend(toHaveNoViolations);

test('dashboard has no accessibility violations', async () => {
  const { container } = render(Dashboard);
  const results = await axe(container);
  expect(results).toHaveNoViolations();
});
```

**Keyboard Navigation Tests:**

```javascript
test('all interactive elements are keyboard accessible', () => {
  const { container } = render(App);
  const interactiveElements = container.querySelectorAll(
    'button, a, input, select, textarea, [tabindex]:not([tabindex="-1"])'
  );
  
  interactiveElements.forEach(el => {
    el.focus();
    expect(document.activeElement).toBe(el);
    expect(window.getComputedStyle(el, ':focus-visible').outlineWidth).not.toBe('0px');
  });
});
```

**Screen Reader Tests:**

```javascript
test('screen reader announcements work correctly', async () => {
  const { getByRole } = render(App);
  const liveRegion = getByRole('status');
  
  // Trigger an action that should announce
  fireEvent.click(getByRole('button', { name: /save/i }));
  
  await waitFor(() => {
    expect(liveRegion).toHaveTextContent('Character saved successfully');
  });
});
```

### Performance Testing

**Lighthouse CI Integration:**

- Run Lighthouse tests in CI pipeline
- Enforce minimum scores (90+ desktop, 80+ mobile)
- Track performance metrics over time
- Alert on performance regressions

**Performance Metrics:**

- First Contentful Paint (FCP) < 1.8s
- Largest Contentful Paint (LCP) < 2.5s
- Time to Interactive (TTI) < 3.8s
- Cumulative Layout Shift (CLS) < 0.1
- First Input Delay (FID) < 100ms

### Visual Regression Testing

**Screenshot Comparison:**

- Capture screenshots of key pages
- Compare against baseline images
- Flag visual changes for review
- Update baselines after approved changes

**Tools:**

- Percy or Chromatic for visual testing
- Playwright for screenshot capture
- Pixel-perfect comparison

### End-to-End Testing

**E2E Test Scenarios:**

- Complete character creation flow
- Training session execution
- Race preparation and execution
- Skill acquisition flow
- Support deck configuration
- Theme switching
- Offline mode functionality

**E2E Testing Framework:**

- Playwright or Cypress for E2E tests
- Test against staging environment
- Run in CI before deployment
- Test across multiple browsers

### Test Coverage Goals

**Coverage Targets:**

- Unit test coverage: 80%+ for components
- Property test coverage: All critical properties tested
- Integration test coverage: All user flows tested
- E2E test coverage: All major features tested
- Accessibility test coverage: 100% of pages tested

### Continuous Integration

**CI Pipeline:**

1. Run unit tests
2. Run property-based tests
3. Run integration tests
4. Run accessibility tests
5. Run Lighthouse performance tests
6. Run E2E tests (on staging)
7. Generate coverage reports
8. Deploy if all tests pass

**Test Execution:**

- Run tests on every pull request
- Run full test suite on main branch
- Run E2E tests before production deployment
- Run visual regression tests on UI changes
