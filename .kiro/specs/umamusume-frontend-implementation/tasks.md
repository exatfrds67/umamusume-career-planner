# Implementation Plan: Umamusume Frontend Implementation

## Overview

This implementation plan breaks down the frontend development into discrete, manageable tasks. Each task builds on previous work and includes testing requirements to ensure quality and correctness. The plan follows a component-first approach, building reusable UI elements before assembling them into complete features.

## Tasks

- [x] 1. Set up frontend infrastructure and design system foundation
  - Configure Tailwind CSS v4 with custom theme variables
  - Set up Alpine.js and initialize global event bus
  - Create base layout templates (app.blade.php, guest.blade.php)
  - Implement responsive breakpoint system
  - Configure Vite for asset bundling
  - _Requirements: 14.1, 14.3_

- [ ]* 1.1 Write property test for responsive breakpoint system
  - **Property 2: Responsive Layout Adaptation**
  - **Validates: Requirements 1.3, 9.2**

- [x] 2. Implement core component library
  - [x] 2.1 Create button components (primary, secondary, outline variants)
    - Implement size variants (sm, md, lg)
    - Add loading and disabled states
    - Include accessibility attributes (aria-label, role)
    - _Requirements: 14.1, 14.2_

  - [ ]* 2.2 Write property test for button component styling
    - **Property 59: Component Styling Consistency**
    - **Validates: Requirements 14.2**

  - [x] 2.3 Create card components (header, body, footer)
    - Implement card variants (default, elevated, outlined)
    - Add responsive padding and spacing
    - _Requirements: 14.1, 14.2_

  - [x] 2.4 Create form components (input, select, checkbox, textarea)
    - Implement validation states (error, success, warning)
    - Add help text and error message display
    - Include ARIA attributes for accessibility
    - _Requirements: 14.4, 8.1, 10.2_

  - [ ]* 2.5 Write property test for form validation
    - **Property 32: Form Validation Errors**
    - **Validates: Requirements 8.1**

  - [x] 2.6 Create badge components (status, grade, rarity)
    - Implement grade badges (SS, S, A, B, C, D, E, F, G)
    - Add color variants for different badge types
    - _Requirements: 14.5, 7.6_

  - [ ]* 2.7 Write property test for grade badge display
    - **Property 31: Grade Badge Display**
    - **Validates: Requirements 7.6**

  - [x] 2.8 Create alert and toast notification components
    - Implement auto-dismiss functionality
    - Add animation transitions
    - Include accessibility announcements
    - _Requirements: 14.6, 16.6_

  - [ ]* 2.9 Write property test for toast auto-dismiss
    - **Property 61: Toast Auto-Dismiss**
    - **Validates: Requirements 14.6**

- [-] 3. Implement accessibility system
  - [x] 3.1 Create accessibility settings panel
    - Implement text size controls (80%-200%)
    - Add high contrast mode toggle
    - Add reduced motion toggle
    - Add keyboard navigation enhancement toggle
    - _Requirements: 19.1, 19.2, 19.3, 19.4_

  - [ ]* 3.2 Write property test for text size scaling
    - **Property 78: Text Size Scaling**
    - **Validates: Requirements 19.2**

  - [x] 3.3 Implement focus management system
    - Create visible focus indicators with 3:1 contrast
    - Implement skip links for main content areas
    - Add keyboard shortcut support
    - _Requirements: 10.1, 1.4, 10.6_

  - [ ]* 3.4 Write property test for keyboard navigation
    - **Property 3: Keyboard Navigation Focus**
    - **Validates: Requirements 1.4, 10.1**

  - [ ] 3.5 Implement ARIA live regions for announcements
    - Create global announcement system
    - Add screen reader announcements for state changes
    - Implement polite and assertive announcement levels
    - _Requirements: 10.2, 16.6_

  - [ ]* 3.6 Write property test for ARIA labels
    - **Property 41: ARIA Labels**
    - **Validates: Requirements 10.2**

  - [ ] 3.7 Implement accessibility preference persistence
    - Save preferences to local storage
    - Restore preferences on page load
    - _Requirements: 19.5_

  - [ ]* 3.8 Write property test for preference persistence
    - **Property 81: Accessibility Preference Persistence**
    - **Validates: Requirements 19.5**

- [ ] 4. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 5. Implement theme and dark mode system
  - [ ] 5.1 Create theme toggle component
    - Implement light/dark/auto theme options
    - Add smooth transition animations
    - _Requirements: 13.1_

  - [ ]* 5.2 Write property test for theme toggle
    - **Property 53: Theme Toggle**
    - **Validates: Requirements 13.1**

  - [ ] 5.3 Implement system theme detection
    - Listen for prefers-color-scheme media query
    - Auto-adapt to system theme changes
    - _Requirements: 13.2_

  - [ ]* 5.4 Write property test for system theme adaptation
    - **Property 54: System Theme Adaptation**
    - **Validates: Requirements 13.2**

  - [ ] 5.5 Implement theme persistence
    - Save theme preference to local storage
    - Restore theme on page load
    - _Requirements: 13.3_

  - [ ]* 5.6 Write property test for theme persistence
    - **Property 55: Theme Persistence**
    - **Validates: Requirements 13.3**

  - [ ] 5.7 Implement dark mode styling
    - Apply dark mode color schemes
    - Load appropriate background images
    - Maintain WCAG AA contrast ratios
    - _Requirements: 13.4, 13.5_

  - [ ]* 5.8 Write property test for contrast ratios
    - **Property 43: Text Contrast Ratios**
    - **Validates: Requirements 10.4**

- [ ] 6. Implement PWA infrastructure
  - [ ] 6.1 Create service worker for offline caching
    - Implement cache-first strategy for static assets
    - Implement network-first strategy for API calls
    - Add background sync for offline actions
    - _Requirements: 11.1, 11.5, 11.6_

  - [ ] 6.2 Create PWA manager class
    - Implement service worker registration
    - Add update notification system
    - Implement offline detection
    - Add connection restoration sync
    - _Requirements: 11.2, 11.3, 11.4_

  - [ ]* 6.3 Write property test for offline indicator
    - **Property 46: Offline Indicator**
    - **Validates: Requirements 11.2**

  - [ ] 6.4 Create web app manifest
    - Configure app name, icons, and theme colors
    - Set display mode to standalone
    - _Requirements: 11.1_

  - [ ] 6.5 Implement offline action queuing
    - Queue actions when offline
    - Sync queued actions on reconnection
    - _Requirements: 11.6_

  - [ ]* 6.6 Write property test for action queuing
    - **Property 49: Offline Action Queuing**
    - **Validates: Requirements 11.6**

- [ ] 7. Implement asset optimization system
  - [ ] 7.1 Create lazy loading system for images
    - Implement Intersection Observer for lazy loading
    - Add blur-up placeholder technique
    - Preload critical images
    - _Requirements: 12.1, 12.2_

  - [ ]* 7.2 Write property test for image lazy loading
    - **Property 50: Image Lazy Loading**
    - **Validates: Requirements 12.1**

  - [ ] 7.3 Implement responsive image system
    - Load appropriate images based on viewport size
    - Load appropriate images based on theme
    - Support WebP format with fallbacks
    - _Requirements: 9.4, 12.6_

  - [ ]* 7.4 Write property test for responsive images
    - **Property 39: Responsive Image Sources**
    - **Validates: Requirements 9.4**

  - [ ] 7.5 Implement character avatar system
    - Create avatar component with size variants
    - Implement lazy loading with blur-up
    - Add fallback for failed image loads
    - Add hover effects for interactive avatars
    - _Requirements: 15.1, 15.2, 15.3, 15.4, 15.6_

  - [ ]* 7.6 Write property test for avatar lazy loading
    - **Property 62: Avatar Lazy Loading**
    - **Validates: Requirements 15.1**

- [ ] 8. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 9. Implement dashboard interface
  - [ ] 9.1 Create dashboard layout
    - Implement two-column grid layout
    - Add responsive behavior for mobile
    - _Requirements: 1.1_

  - [ ] 9.2 Create character status card component
    - Display character stats with grade badges
    - Show mood and energy indicators
    - Display current turn number
    - _Requirements: 1.1, 7.1_

  - [ ]* 9.3 Write property test for stat visualization
    - **Property 27: Stat Visualization**
    - **Validates: Requirements 7.1**

  - [ ] 9.4 Create goals progress panel component
    - Display goal progress bars
    - Show goal status indicators (on track/at risk/off track)
    - Add edit goals button
    - _Requirements: 1.1, 7.3_

  - [ ]* 9.5 Write property test for goal progress display
    - **Property 29: Goal Progress Display**
    - **Validates: Requirements 7.3**

  - [ ] 9.6 Create upcoming races list component
    - Display next 3 races with details
    - Show readiness assessment
    - Add race preparation links
    - _Requirements: 1.1_

  - [ ] 9.7 Create training suggestions component
    - Display top 3 training recommendations
    - Show predicted gains and risk
    - Highlight AI recommendations
    - _Requirements: 1.1, 3.2_

  - [ ] 9.8 Create support deck preview component
    - Display 6-card deck overview
    - Show bond levels
    - Add edit deck link
    - _Requirements: 1.1_

  - [ ] 9.9 Implement navigation system
    - Create sidebar navigation
    - Implement active state management
    - Add mobile bottom navigation
    - _Requirements: 1.2_

  - [ ]* 9.10 Write property test for navigation state
    - **Property 1: Navigation State Consistency**
    - **Validates: Requirements 1.2**

- [ ] 10. Implement character creation wizard
  - [ ] 10.1 Create wizard stepper component
    - Implement 4-step progress indicator
    - Add step validation
    - Enable backward navigation
    - _Requirements: 2.1_

  - [ ] 10.2 Create trainee selection step (Step 1)
    - Implement search and filter interface
    - Create trainee card grid
    - Add preview panel with base stats
    - Add scenario dropdown
    - _Requirements: 2.2_

  - [ ]* 10.3 Write property test for trainee display
    - **Property 5: Trainee Selection Display**
    - **Validates: Requirements 2.2**

  - [ ] 10.4 Create parent selection step (Step 2)
    - Implement two-column parent picker
    - Create inheritance preview calculator
    - Display stat factors and growth rates
    - _Requirements: 2.3_

  - [ ]* 10.5 Write property test for inheritance calculation
    - **Property 6: Inheritance Calculation**
    - **Validates: Requirements 2.3**

  - [ ] 10.6 Create support deck configuration step (Step 3)
    - Implement 6-slot card grid
    - Add drag-and-drop functionality
    - Display synergy score
    - Add auto-fill suggestions
    - _Requirements: 2.1_

  - [ ] 10.7 Create review and confirmation step (Step 4)
    - Display summary of all selections
    - Show projected starting stats
    - Add goal recommendations
    - Implement create character action
    - _Requirements: 2.5_

  - [ ] 10.8 Implement wizard state management
    - Persist data across steps
    - Enable backward navigation without data loss
    - Validate each step before proceeding
    - _Requirements: 2.6_

  - [ ]* 10.9 Write property test for wizard state persistence
    - **Property 8: Wizard State Persistence**
    - **Validates: Requirements 2.6**

- [ ] 11. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 12. Implement training selection interface
  - [ ] 12.1 Create training options display
    - Display 5 training facilities + race option
    - Show predicted stat gains
    - Display risk assessment badges
    - Show support card participation
    - Mark skill hint availability
    - _Requirements: 3.1_

  - [ ]* 12.2 Write property test for training options display
    - **Property 9: Training Options Display**
    - **Validates: Requirements 3.1**

  - [ ] 12.3 Implement AI recommendation highlighting
    - Highlight recommended training
    - Display AI reasoning
    - Show efficiency score
    - _Requirements: 3.2_

  - [ ]* 12.4 Write property test for AI recommendation
    - **Property 10: AI Recommendation Highlighting**
    - **Validates: Requirements 3.2**

  - [ ] 12.5 Create training selection details panel
    - Display skill hints with probabilities
    - Show support card bonuses
    - Display mood and energy predictions
    - _Requirements: 3.3_

  - [ ]* 12.6 Write property test for training details
    - **Property 11: Training Selection Details**
    - **Validates: Requirements 3.3**

  - [ ] 12.7 Implement training confirmation modal
    - Display final predictions
    - Show risk warnings if applicable
    - Add confirm/cancel buttons
    - _Requirements: 3.6_

  - [ ]* 12.8 Write property test for training warnings
    - **Property 12: Training Impact Warnings**
    - **Validates: Requirements 3.6**

  - [ ] 12.9 Create training history display
    - Show stat progression charts
    - Display recent training results
    - _Requirements: 3.5_

- [ ] 13. Implement race preparation interface
  - [ ] 13.1 Create race analysis panel
    - Display race details (grade, distance, surface, track)
    - Show stat requirements with readiness indicators
    - Display running style recommendations
    - Show skill recommendations
    - Display performance forecast
    - _Requirements: 4.1_

  - [ ]* 13.2 Write property test for race details display
    - **Property 13: Race Details Display**
    - **Validates: Requirements 4.1**

  - [ ] 13.3 Create strategy selection interface
    - Display multiple strategy options
    - Show success probabilities
    - Highlight AI-recommended strategy
    - Display strategy reasoning
    - _Requirements: 4.2_

  - [ ]* 13.4 Write property test for strategy options
    - **Property 14: Race Strategy Options**
    - **Validates: Requirements 4.2**

  - [ ] 13.5 Implement stat gap analysis
    - Identify stat deficiencies
    - Recommend training focus
    - Show training timeline to close gaps
    - _Requirements: 4.3_

  - [ ]* 13.6 Write property test for stat gap recommendations
    - **Property 15: Stat Gap Recommendations**
    - **Validates: Requirements 4.3**

  - [ ] 13.7 Create race execution interface
    - Implement race animation (skippable)
    - Display real-time position updates
    - Show final results with grade achieved
    - Display rewards (fans, grade points, items)
    - _Requirements: 4.4_

  - [ ] 13.8 Create race history display
    - Show performance analytics
    - Display race results timeline
    - _Requirements: 4.5_

- [ ] 14. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 15. Implement skill management interface
  - [ ] 15.1 Create skill inventory display
    - Show owned skills list
    - Display available skills grid
    - Show SP balance
    - _Requirements: 5.1_

  - [ ]* 15.2 Write property test for skill inventory
    - **Property 16: Skill Inventory Display**
    - **Validates: Requirements 5.1**

  - [ ] 15.3 Implement skill selection and cost display
    - Show base cost and hint reductions
    - Calculate and display final cost
    - Show skill effects and rarity
    - _Requirements: 5.2_

  - [ ]* 15.4 Write property test for skill cost calculation
    - **Property 17: Skill Cost Calculation**
    - **Validates: Requirements 5.2**

  - [ ] 15.5 Create skill evolution interface
    - Display evolution options
    - Show cost and benefit comparison
    - Add evolution confirmation
    - _Requirements: 5.3_

  - [ ]* 15.6 Write property test for skill evolution
    - **Property 18: Skill Evolution Offering**
    - **Validates: Requirements 5.3**

  - [ ] 15.7 Implement skill filters and sorting
    - Add ownership filter (owned/available/all)
    - Add rarity filter
    - Add category filter
    - Implement sort options (cost/name/rarity)
    - _Requirements: 5.5_

  - [ ]* 15.8 Write property test for skill filtering
    - **Property 19: Skill Filtering**
    - **Validates: Requirements 5.5**

  - [ ] 15.9 Implement skill acquisition flow
    - Deduct SP on acquisition
    - Add skill to inventory
    - Check evolution eligibility
    - _Requirements: 5.4_

- [ ] 16. Implement support card deck builder
  - [ ] 16.1 Create support deck display
    - Show 6-card grid with current deck
    - Display bond levels
    - Show card specializations
    - _Requirements: 6.1_

  - [ ]* 16.2 Write property test for deck display
    - **Property 21: Support Deck Display**
    - **Validates: Requirements 6.1**

  - [ ] 16.3 Implement drag-and-drop card swapping
    - Enable card dragging
    - Update deck on drop
    - Recalculate synergy in real-time
    - _Requirements: 6.2_

  - [ ]* 16.4 Write property test for drag-and-drop
    - **Property 22: Deck Drag-and-Drop**
    - **Validates: Requirements 6.2**

  - [ ] 16.5 Implement synergy calculation
    - Calculate type distribution score
    - Calculate bond level average
    - Count meta cards
    - Display overall synergy rating
    - Show optimization suggestions
    - _Requirements: 6.3_

  - [ ]* 16.6 Write property test for synergy recalculation
    - **Property 23: Synergy Recalculation**
    - **Validates: Requirements 6.3**

  - [ ] 16.7 Create card details panel
    - Display skill provisions
    - Show bond bonuses
    - Display meta tier ranking
    - _Requirements: 6.4_

  - [ ]* 16.8 Write property test for card details
    - **Property 24: Card Details Display**
    - **Validates: Requirements 6.4**

  - [ ] 16.9 Implement card collection browser
    - Show all owned cards
    - Add filters (rarity, type, bond level)
    - Enable card selection for deck
    - _Requirements: 6.1_

- [ ] 17. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 18. Implement stat visualization components
  - [ ] 18.1 Create stat bar component
    - Display current value and grade
    - Show target goal
    - Implement smooth animations on value change
    - _Requirements: 7.1, 7.2_

  - [ ]* 18.2 Write property test for stat animation
    - **Property 28: Stat Animation**
    - **Validates: Requirements 7.2**

  - [ ] 18.3 Create stat gap highlighting
    - Identify stats below target
    - Display warning indicators
    - Show gap amount
    - _Requirements: 7.4_

  - [ ]* 18.4 Write property test for stat gap highlighting
    - **Property 30: Stat Gap Highlighting**
    - **Validates: Requirements 7.4**

  - [ ] 18.5 Create stat history chart component
    - Display stat progression over time
    - Show training impact on stats
    - _Requirements: 7.5_

- [ ] 19. Implement form validation and error handling
  - [ ] 19.1 Create form validation system
    - Implement real-time validation on blur
    - Display inline error messages
    - Apply error styling to invalid fields
    - _Requirements: 8.1, 8.3_

  - [ ]* 19.2 Write property test for validation errors
    - **Property 32: Form Validation Errors**
    - **Validates: Requirements 8.1**

  - [ ] 19.3 Implement error focus management
    - Focus first invalid field on submission
    - Announce errors to screen readers
    - _Requirements: 8.2_

  - [ ]* 19.4 Write property test for error focus
    - **Property 33: Error Focus and Announcement**
    - **Validates: Requirements 8.2**

  - [ ] 19.5 Create error boundary components
    - Implement application-level error boundary
    - Implement route-level error boundaries
    - Implement component-level error boundaries
    - Display fallback UI with recovery options
    - _Requirements: 20.1, 20.5_

  - [ ]* 19.6 Write property test for error boundaries
    - **Property 83: Error Boundary Display**
    - **Validates: Requirements 20.1**

  - [ ] 19.7 Implement API error handling
    - Display user-friendly error messages
    - Provide recovery options
    - Log errors for debugging
    - _Requirements: 20.2, 20.4_

  - [ ]* 19.8 Write property test for API errors
    - **Property 84: API Error Messages**
    - **Validates: Requirements 20.2**

- [ ] 20. Implement search and filter functionality
  - [ ] 20.1 Create search component
    - Implement real-time search filtering
    - Add search input with clear button
    - Display search results count
    - _Requirements: 17.1_

  - [ ]* 20.2 Write property test for real-time search
    - **Property 69: Real-time Search Filtering**
    - **Validates: Requirements 17.1**

  - [ ] 20.3 Create filter component
    - Implement multiple filter criteria
    - Update results without page reload
    - Display active filter badges
    - Add clear all filters option
    - _Requirements: 17.2, 17.6_

  - [ ]* 20.4 Write property test for filter application
    - **Property 70: Filter Application**
    - **Validates: Requirements 17.2**

  - [ ] 20.5 Implement empty state display
    - Show "no results" message
    - Provide search suggestions
    - Add clear search button
    - _Requirements: 17.3_

- [ ] 21. Implement animation and transition system
  - [ ] 21.1 Create fade-in animations for UI elements
    - Apply fade-in on element appearance
    - Use CSS transitions for smooth effects
    - _Requirements: 18.1_

  - [ ]* 21.2 Write property test for fade-in animations
    - **Property 73: Element Fade-in**
    - **Validates: Requirements 18.1**

  - [ ] 21.3 Create modal transition animations
    - Implement scale and fade transitions
    - Add backdrop fade effect
    - _Requirements: 18.3_

  - [ ]* 21.4 Write property test for modal transitions
    - **Property 75: Modal Transitions**
    - **Validates: Requirements 18.3**

  - [ ] 21.5 Implement reduced motion support
    - Detect prefers-reduced-motion preference
    - Disable non-essential animations
    - Maintain functionality without animations
    - _Requirements: 12.4, 18.5_

  - [ ]* 21.6 Write property test for reduced motion
    - **Property 51: Reduced Motion Respect**
    - **Validates: Requirements 12.4**

- [ ] 22. Implement WebSocket real-time updates
  - [ ] 22.1 Configure Laravel Echo
    - Set up Echo with Pusher/Socket.io
    - Create event listeners for updates
    - _Requirements: 16.5_

  - [ ] 22.2 Implement real-time stat updates
    - Listen for training completion events
    - Update stats without page refresh
    - _Requirements: 16.1_

  - [ ] 22.3 Implement race result updates
    - Listen for race completion events
    - Push updates to client
    - _Requirements: 16.2_

  - [ ]* 22.4 Write property test for WebSocket updates
    - **Property 66: WebSocket Updates**
    - **Validates: Requirements 16.2**

  - [ ] 22.5 Implement connection recovery
    - Detect connection loss
    - Attempt reconnection with exponential backoff
    - _Requirements: 16.4_

  - [ ]* 22.6 Write property test for reconnection
    - **Property 67: WebSocket Reconnection**
    - **Validates: Requirements 16.4**

- [ ] 23. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 24. Integration and polish
  - [ ] 24.1 Integrate all components into complete pages
    - Wire dashboard components together
    - Connect wizard steps
    - Link training and race interfaces
    - Connect skill and support card management
    - _Requirements: All_

  - [ ] 24.2 Implement page transitions and loading states
    - Add loading spinners for async operations
    - Implement skeleton screens for content loading
    - Add page transition animations
    - _Requirements: 18.4_

  - [ ]* 24.3 Write property test for loading states
    - **Property 76: Page Loading States**
    - **Validates: Requirements 18.4**

  - [ ] 24.4 Optimize performance
    - Implement code splitting for routes
    - Optimize bundle sizes
    - Preload critical assets
    - Run Lighthouse performance audit
    - _Requirements: 12.2, 12.5_

  - [ ] 24.5 Run accessibility audit
    - Test with screen readers
    - Verify keyboard navigation
    - Check color contrast ratios
    - Run axe accessibility tests
    - _Requirements: 10.1, 10.2, 10.3, 10.4_

  - [ ] 24.6 Test PWA functionality
    - Verify offline mode works
    - Test service worker caching
    - Verify update notifications
    - Test action queuing and sync
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.6_

  - [ ] 24.7 Cross-browser testing
    - Test on Chrome, Firefox, Safari, Edge
    - Test on mobile browsers (iOS Safari, Chrome Mobile)
    - Fix browser-specific issues
    - _Requirements: All_

  - [ ] 24.8 Visual regression testing
    - Capture baseline screenshots
    - Run visual comparison tests
    - Review and approve visual changes
    - _Requirements: All_

## Notes

- Tasks marked with `*` are optional property-based tests that can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- Integration tasks wire everything together at the end
