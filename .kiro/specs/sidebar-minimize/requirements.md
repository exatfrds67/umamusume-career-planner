# Sidebar Minimize Feature - Requirements Document

**Feature Name**: sidebar-minimize  
**Document Type**: Requirements  
**Version**: 1.0.0  
**Date**: February 8, 2026  
**Status**: Planning - Ready for Implementation  
**Related Documents**:

- [Implementation Plan](../../../docs/frontend-development/sidebar-minimize-implementation-plan.md)
- [Visual Reference](../../../docs/frontend-development/sidebar-minimize-visual-reference.md)
- [Summary](../../../docs/frontend-development/SIDEBAR-MINIMIZE-SUMMARY.md)

---

## 1. Feature Overview

### 1.1 Purpose

Add a collapsible sidebar feature to the Umamusume Career Planner application that allows users to minimize the navigation sidebar to gain more screen real estate while maintaining full accessibility and navigation functionality.

### 1.2 Goals

- Provide users with control over sidebar visibility on desktop screens (≥1024px)
- Persist user preference across sessions using localStorage
- Maintain WCAG 2.2 AA accessibility compliance
- Ensure smooth transitions and responsive behavior
- Preserve existing mobile and tablet navigation patterns

### 1.3 Success Criteria

- Users can toggle sidebar between expanded (288px) and minimized (80px) states
- Sidebar state persists across browser sessions
- All navigation remains accessible in both states
- Transitions are smooth (300ms) without layout shifts
- Feature passes all accessibility tests (keyboard navigation, screen readers)
- No performance degradation (< 5ms impact on page load)

---

## 2. User Stories

### 2.1 Primary User Stories

**US-1: Toggle Sidebar State**

- **As a** desktop user
- **I want to** minimize the sidebar to an icon-only view
- **So that** I can maximize screen space for content-heavy pages

**Acceptance Criteria:**

- Given I am on any page with the sidebar visible
- When I click the toggle button at the bottom of the sidebar
- Then the sidebar collapses to 80px width showing only icons
- And the main content area expands to fill the available space
- And the toggle button icon changes to indicate expand action

**US-2: Persist Sidebar Preference**

- **As a** returning user
- **I want to** have my sidebar preference remembered
- **So that** I don't have to re-minimize it on every visit

**Acceptance Criteria:**

- Given I have minimized the sidebar
- When I navigate to another page or refresh the browser
- Then the sidebar remains in the minimized state
- And my preference is stored in localStorage

**US-3: Navigate with Minimized Sidebar**

- **As a** user with a minimized sidebar
- **I want to** see navigation labels on hover
- **So that** I can identify navigation items without expanding the sidebar

**Acceptance Criteria:**

- Given the sidebar is minimized
- When I hover over a navigation icon
- Then a tooltip appears showing the navigation label
- And the tooltip is positioned to the right of the icon
- And the tooltip disappears when I move my mouse away

**US-4: Keyboard Navigation**

- **As a** keyboard user
- **I want to** toggle the sidebar using keyboard only
- **So that** I can use the feature without a mouse

**Acceptance Criteria:**

- Given I am navigating with keyboard
- When I tab to the toggle button and press Enter or Space
- Then the sidebar toggles between expanded and minimized states
- And focus remains on the toggle button
- And screen readers announce the state change

### 2.2 Secondary User Stories

**US-5: Mobile Behavior Unchanged**

- **As a** mobile user
- **I want to** continue using the existing overlay sidebar
- **So that** my mobile experience is not disrupted

**Acceptance Criteria:**

- Given I am on a mobile device (< 1024px)
- When I open the sidebar
- Then it appears as a full-width overlay (existing behavior)
- And the minimize toggle is not visible
- And the sidebar closes when I tap outside

**US-6: Responsive Breakpoints**

- **As a** user on different screen sizes
- **I want to** have appropriate sidebar behavior for my device
- **So that** the interface is optimized for my viewport

**Acceptance Criteria:**

- Given I am on mobile (< 640px)
- Then I see the bottom navigation bar (no sidebar)
- Given I am on tablet (640-1024px)
- Then I see the overlay sidebar with hamburger menu
- Given I am on desktop (≥ 1024px)
- Then I see the fixed sidebar with minimize toggle

---

## 3. Functional Requirements

### 3.1 Core Functionality

**FR-1: Sidebar Toggle Mechanism**

- The sidebar must have a toggle button positioned at the top-right of the header
- The button must appear on hover over the sidebar header area (fade-in 200ms)
- The button must display a chevron-double-left icon (<<) when expanded
- The button must display a chevron-double-right icon (>>) when minimized
- The button must show a tooltip on hover: "Minimize sidebar" or "Expand sidebar"
- Clicking the button must toggle between expanded and minimized states
- The transition must be smooth (300ms duration)
- The button must remain keyboard accessible even when not visible

**FR-2: Expanded State Specifications**

- Sidebar width: 288px (w-72)
- Logo: Full "Umamusume Career Planner" text with icon (40x40px, always visible)
- Logo text: Visible below/beside icon
- Toggle button: Hidden until header hover, positioned top-right
- Navigation items: Icon (24x24px) + text label
- Collapsible groups: Expandable with chevron indicators
- User menu: Avatar + name + email (if authenticated)

**FR-3: Minimized State Specifications**

- Sidebar width: 80px (w-20)
- Logo: Icon only (40x40px, centered, always visible, never cropped)
- Logo text: Hidden
- Toggle button: Hidden until header hover, positioned top-right
- Navigation items: Icon only (28x28px, larger for better visibility)
- Icon spacing: More generous padding for cleaner look
- Collapsible groups: Hidden (show in tooltip on hover)
- User menu: Avatar only (show details in tooltip)

**FR-4: State Persistence**

- Sidebar state must be stored in localStorage with key `sidebar-minimized`
- State must be restored on page load
- State must persist across browser sessions
- State must be boolean: `true` (minimized) or `false` (expanded)

**FR-5: Tooltip System**

- Tooltips must appear on hover over navigation icons when minimized
- Tooltips must display the full navigation label
- Tooltips must be positioned to the right of the icon
- Tooltips must have a subtle arrow pointing to the icon
- Tooltips must appear after 200ms hover delay
- Tooltips must disappear immediately on mouse leave

### 3.2 Layout Adjustments

**FR-6: Main Content Area**

- When sidebar is expanded: `lg:pl-72` (288px left padding)
- When sidebar is minimized: `lg:pl-20` (80px left padding)
- Transition must be synchronized with sidebar width change
- No content reflow or layout shift during transition

**FR-7: Responsive Behavior**

- Mobile (< 640px): Bottom navigation bar (no sidebar)
- Tablet (640-1024px): Overlay sidebar (existing behavior)
- Desktop (≥ 1024px): Fixed sidebar with minimize toggle
- Toggle button only visible on desktop breakpoint

### 3.3 Accessibility Requirements

**FR-8: Keyboard Navigation**

- Toggle button must be keyboard accessible (Tab key)
- Toggle button must respond to Enter and Space keys
- Focus indicator must be clearly visible (3:1 contrast minimum)
- Focus must remain on toggle button after activation
- All navigation items must remain keyboard accessible in both states

**FR-9: Screen Reader Support**

- Toggle button must have `aria-label`: "Minimize sidebar" / "Expand sidebar"
- Toggle button must have `aria-pressed` attribute reflecting state
- Sidebar container must have `aria-expanded` attribute
- State changes must be announced via `aria-live` region
- Tooltips must have `role="tooltip"` attribute

**FR-10: WCAG 2.2 AA Compliance**

- Color contrast: Minimum 4.5:1 for text, 3:1 for UI components
- Touch targets: Minimum 44x44px for mobile (if applicable)
- Motion: Respect `prefers-reduced-motion` user preference
- Focus indicators: Visible on all interactive elements
- Text alternatives: All icons must have accessible labels

---

## 4. Non-Functional Requirements

### 4.1 Performance

**NFR-1: Animation Performance**

- Toggle transition duration: 300ms
- Transition must use GPU-accelerated properties (transform, opacity)
- No layout thrashing or forced reflows
- Smooth 60fps animation on modern browsers

**NFR-2: Load Time Impact**

- Feature must add < 5ms to initial page load time
- localStorage read must be synchronous and fast (< 10ms)
- No blocking JavaScript during initialization

**NFR-3: Memory Footprint**

- Additional JavaScript: < 2KB minified
- Additional CSS: < 1KB
- localStorage usage: < 100 bytes
- No memory leaks from event listeners

### 4.2 Browser Compatibility

**NFR-4: Supported Browsers**

- Chrome/Edge: Latest 2 versions
- Firefox: Latest 2 versions
- Safari: Latest 2 versions
- Mobile Safari: iOS 14+
- Chrome Mobile: Android 10+

**NFR-5: Graceful Degradation**

- If JavaScript is disabled: Sidebar defaults to expanded state
- If localStorage is unavailable: Feature works but doesn't persist
- If CSS transitions are unsupported: Instant toggle without animation

### 4.3 Maintainability

**NFR-6: Code Quality**

- Follow Laravel Blade component conventions
- Use Alpine.js for state management (no jQuery)
- Use Tailwind CSS v4 utility classes
- Follow project coding standards (PSR-12, AGENTS.md guidelines)

**NFR-7: Documentation**

- Inline code comments for complex logic
- User-facing documentation in help section
- Developer documentation for extending feature
- Accessibility documentation for testing

---

## 5. Technical Specifications

### 5.1 Technology Stack

- **Frontend Framework**: Alpine.js 3.x
- **Styling**: Tailwind CSS v4
- **Storage**: Browser localStorage API
- **Icons**: Heroicons (existing project standard)
- **Testing**: Pest 4 (browser tests), Playwright (E2E)

### 5.2 State Management

**Alpine.js Store Structure:**

```javascript
Alpine.store('sidebar', {
    minimized: localStorage.getItem('sidebar-minimized') === 'true',
    
    toggle() {
        this.minimized = !this.minimized;
        localStorage.setItem('sidebar-minimized', this.minimized);
        this.announce();
    },
    
    expand() {
        this.minimized = false;
        localStorage.setItem('sidebar-minimized', false);
        this.announce();
    },
    
    minimize() {
        this.minimized = true;
        localStorage.setItem('sidebar-minimized', true);
        this.announce();
    },
    
    announce() {
        const message = this.minimized ? 'Sidebar minimized' : 'Sidebar expanded';
        const announcer = document.getElementById('sidebar-announcer');
        if (announcer) {
            announcer.textContent = message;
        }
    }
});
```

### 5.3 Component Structure

**Files to Modify:**

1. `resources/views/layouts/app.blade.php`
   - Add Alpine.js store initialization
   - Update sidebar container classes
   - Update main content padding classes

2. `resources/views/components/app/sidebar.blade.php`
   - Add toggle button component
   - Add conditional rendering for labels
   - Add tooltip components for minimized state

3. `resources/js/app.js`
   - Register Alpine.js store
   - Initialize sidebar state on page load

4. `resources/css/app.css` (if needed)
   - Custom transition utilities
   - Tooltip styles

**New Components:**

1. `resources/views/components/sidebar-tooltip.blade.php`
   - Reusable tooltip component for navigation items

### 5.4 CSS Classes

**Sidebar Container:**

- Expanded: `lg:w-72` (288px)
- Minimized: `lg:w-20` (80px)
- Transition: `transition-all duration-300 ease-in-out`

**Main Content:**

- Expanded: `lg:pl-72` (288px padding)
- Minimized: `lg:pl-20` (80px padding)
- Transition: `transition-all duration-300 ease-in-out`

**Toggle Button:**

- Position: Bottom of sidebar
- Size: `h-12 w-full`
- Hover: `hover:bg-gray-100 dark:hover:bg-gray-700`

---

## 6. Acceptance Criteria Summary

### 6.1 Must Have (MVP)

- [ ] Toggle button visible on desktop (≥1024px)
- [ ] Sidebar toggles between 288px and 80px width
- [ ] Main content adjusts padding accordingly
- [ ] State persists in localStorage
- [ ] Tooltips show on hover in minimized state
- [ ] Keyboard accessible (Tab, Enter, Space)
- [ ] Screen reader announces state changes
- [ ] Smooth 300ms transition animation
- [ ] No layout shifts during transition
- [ ] Mobile/tablet behavior unchanged

### 6.2 Should Have

- [ ] Focus management (focus stays on toggle button)
- [ ] Reduced motion support (prefers-reduced-motion)
- [ ] Cross-browser testing (Chrome, Firefox, Safari, Edge)
- [ ] Automated accessibility tests (axe-core)
- [ ] Visual regression tests (Playwright)

### 6.3 Could Have (Future Enhancements)

- [ ] Keyboard shortcut (e.g., Alt+B)
- [ ] Hover-to-expand behavior
- [ ] Custom sidebar width settings
- [ ] Pinned navigation items
- [ ] Animation presets (slide, fade, scale)

---

## 7. Testing Requirements

### 7.1 Unit Tests (Pest)

```php
// tests/Feature/SidebarMinimizeTest.php

it('persists sidebar state to localStorage')
it('restores sidebar state from localStorage')
it('toggles sidebar width classes')
it('updates main content padding')
it('shows tooltips in minimized state')
it('hides tooltips in expanded state')
```

### 7.2 Browser Tests (Pest 4)

```php
// tests/Browser/SidebarMinimizeTest.php

it('toggles sidebar with mouse click')
it('toggles sidebar with keyboard')
it('maintains navigation functionality when minimized')
it('shows tooltips on hover')
it('persists state across page navigation')
it('respects mobile breakpoint behavior')
```

### 7.3 Accessibility Tests (Playwright)

```javascript
// tests/e2e/accessibility/sidebar-minimize.spec.js

test('has no accessibility violations (expanded)')
test('has no accessibility violations (minimized)')
test('toggle button has correct ARIA attributes')
test('announces state changes to screen readers')
test('maintains keyboard navigation flow')
test('respects prefers-reduced-motion')
```

### 7.4 Visual Regression Tests

```javascript
// tests/e2e/visual/sidebar-states.spec.js

test('expanded state matches snapshot')
test('minimized state matches snapshot')
test('transition animation is smooth')
test('tooltips render correctly')
```

---

## 8. Dependencies

### 8.1 External Dependencies

- Alpine.js 3.x (already in project)
- Tailwind CSS v4 (already in project)
- Heroicons (already in project)
- Browser localStorage API (native)

### 8.2 Internal Dependencies

- `resources/views/layouts/app.blade.php` (existing layout)
- `resources/views/components/app/sidebar.blade.php` (existing sidebar)
- `resources/js/app.js` (existing Alpine.js initialization)

### 8.3 No Breaking Changes

- Existing mobile/tablet behavior preserved
- Existing navigation structure unchanged
- Existing routing and authentication unchanged
- Existing accessibility features maintained

---

## 9. Risks and Mitigations

### 9.1 Technical Risks

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| Layout shift during animation | Medium | Low | Use transform instead of width changes |
| localStorage quota exceeded | Low | Very Low | Minimal data storage (< 100 bytes) |
| Alpine.js store conflicts | Medium | Low | Use unique store namespace |
| Tooltip positioning issues | Low | Medium | Use robust positioning logic |
| Browser compatibility issues | Medium | Low | Test on all supported browsers |

### 9.2 UX Risks

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| Users can't find navigation | High | Low | Clear tooltips and recognizable icons |
| Confusion about toggle button | Medium | Low | Clear icon and accessible label |
| Mobile overlay conflicts | Medium | Low | Thorough mobile testing |
| Accessibility barriers | High | Low | Comprehensive a11y testing |

---

## 10. Implementation Phases

### Phase 1: Core Functionality (Priority: High)

**Estimated Effort**: 4-6 hours

- Create Alpine.js store for sidebar state
- Implement toggle button with icon switching
- Add width transitions to sidebar container
- Implement localStorage persistence
- Add conditional rendering for expanded/minimized content

### Phase 2: Navigation Enhancement (Priority: High)

**Estimated Effort**: 3-4 hours

- Implement tooltip system for minimized icons
- Add hover states and focus indicators
- Ensure active route highlighting works in both states
- Test keyboard navigation flow

### Phase 3: Responsive Behavior (Priority: Medium)

**Estimated Effort**: 3-4 hours

- Verify breakpoint behavior (mobile, tablet, desktop)
- Test mobile overlay behavior
- Add touch gesture support (if needed)
- Test across viewport sizes

### Phase 4: Accessibility & Polish (Priority: High)

**Estimated Effort**: 2-3 hours

- Add ARIA labels and live regions
- Test with screen readers (NVDA, JAWS, VoiceOver)
- Add focus management for toggle action
- Verify WCAG 2.2 AA compliance
- Add reduced motion support

### Phase 5: Testing & Documentation (Priority: High)

**Estimated Effort**: 2-3 hours

- Write Pest browser tests
- Write Playwright accessibility tests
- Write visual regression tests
- Document user-facing behavior
- Create developer documentation

**Total Estimated Effort**: 14-20 hours

---

## 11. Success Metrics

### 11.1 Quantitative Metrics

- **Adoption Rate**: % of desktop users who minimize sidebar at least once
- **Persistence Rate**: % of users who keep sidebar minimized across sessions
- **Performance Impact**: < 5ms added to page load time
- **Accessibility Score**: Maintain 100% Lighthouse accessibility score
- **Error Rate**: < 0.1% JavaScript errors related to feature

### 11.2 Qualitative Metrics

- **User Feedback**: Positive sentiment in user surveys
- **Support Tickets**: No increase in navigation-related issues
- **Usability Testing**: 90%+ task completion rate with minimized sidebar
- **Developer Feedback**: Easy to maintain and extend

---

## 12. Rollout Plan

### 12.1 Development Phase (Week 1)

- Implement core functionality
- Add navigation enhancements
- Implement responsive behavior
- Daily testing and iteration

### 12.2 Testing Phase (Week 2)

- Write and run automated tests
- Conduct manual accessibility testing
- Perform cross-browser testing
- User acceptance testing (internal team)

### 12.3 Deployment Phase (Week 3)

- Deploy to staging environment
- Monitor for issues and gather feedback
- Deploy to production (low-traffic period)
- Monitor analytics and error logs

### 12.4 Post-Launch (Week 4+)

- Gather user feedback
- Address any issues or bugs
- Plan Phase 2 enhancements
- Document lessons learned

---

## 13. Approval and Sign-off

### 13.1 Stakeholders

- [ ] Product Manager - Requirements approval
- [ ] Lead Developer - Technical feasibility
- [ ] UX Designer - Design approval
- [ ] Accessibility Specialist - WCAG compliance
- [ ] QA Lead - Testing strategy

### 13.2 Definition of Done

A feature is considered complete when:

- [ ] All acceptance criteria are met
- [ ] All automated tests pass
- [ ] Manual accessibility testing complete
- [ ] Cross-browser testing complete
- [ ] Documentation complete
- [ ] Code review approved
- [ ] Deployed to production
- [ ] No critical bugs reported

---

## 14. Related Documentation

- [Implementation Plan](../../../docs/frontend-development/sidebar-minimize-implementation-plan.md)
- [Visual Reference](../../../docs/frontend-development/sidebar-minimize-visual-reference.md)
- [Summary](../../../docs/frontend-development/SIDEBAR-MINIMIZE-SUMMARY.md)
- [Product Overview](../../../.amazonq/rules/memory-bank/product.md)
- [Technology Stack](../../../.amazonq/rules/memory-bank/tech.md)
- [Project Structure](../../../.amazonq/rules/memory-bank/structure.md)
- [Development Guidelines](../../../AGENTS.md)

---

## 15. Document Control

**Version History:**

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0.0 | 2026-02-08 | Development Team | Initial requirements document |

**Next Review**: Post-implementation (Week 4)

**Status**: Planning Complete - Ready for Implementation
