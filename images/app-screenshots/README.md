# Umamusume Career Planner - Application Screenshots

This directory contains comprehensive screenshots of all pages in the Umamusume Career Planner application (v2.0.0).

**Captured on:** January 26, 2026  
**Application Version:** 2.0.0  
**Laravel Version:** v12  
**PHP Version:** 8.4.11

---

## Screenshot Index

### Public Pages (Unauthenticated)

| # | Filename | Page | Description |
|---|----------|------|-------------|
| 01 | `01-welcome-page.png` | Welcome / Landing | Main landing page with feature highlights |
| 02 | `02-about-page.png` | About | Application information and features |
| 03 | `03-login-page.png` | Login | User authentication page |
| 04 | `04-register-page.png` | Register | New user registration |

### Authenticated Pages - Main Features

| # | Filename | Page | Description |
|---|----------|------|-------------|
| 05 | `05-dashboard-page.png` | Dashboard | Main dashboard with character overview |
| 06 | `06-characters-list-page.png` | Characters List | Grid view of all characters |
| 07 | `07-character-create-page.png` | Character Create | Character creation wizard |
| 08 | `08-character-detail-page.png` | Character Detail | Individual character details |
| 09 | `09-training-predictions-page.png` | Training Predictions | AI-powered training recommendations |
| 10 | `10-races-page.png` | Races | Race schedule and management |
| 11 | `11-skills-page.png` | Skills | Skill catalog and management |
| 12 | `12-support-cards-page.png` | Support Cards | Support card collection |

### AI & Integration Features

| # | Filename | Page | Description |
|---|----------|------|-------------|
| 13 | `13-ai-chat-page.png` | AI Chat | Conversational AI advisor |
| 14 | `14-ai-dashboard-page.png` | AI Dashboard | AI metrics and monitoring |
| 15 | `15-mcp-dashboard-page.png` | MCP Dashboard | Model Context Protocol monitoring |

### Data Management

| # | Filename | Page | Description |
|---|----------|------|-------------|
| 16 | `16-ocr-upload-page.png` | OCR Upload | Screenshot processing and extraction |
| 17 | `17-data-import-page.png` | Data Import | Import data from various formats |
| 18 | `18-data-export-page.png` | Data Export | Export data to JSON/CSV/Excel |
| 19 | `19-data-management-hub-page.png` | Data Management Hub | Central data operations hub |
| 20 | `20-external-data-browse-page.png` | External Data Browser | Browse umapyoi.net API data |

### User & System Pages

| # | Filename | Page | Description |
|---|----------|------|-------------|
| 21 | `21-profile-page.png` | Profile | User profile and settings |
| 22 | `22-settings-page.png` | Settings | Application settings |
| 23 | `23-reports-page.png` | Reports | Career reports and analytics |
| 24 | `24-historical-tracking-page.png` | Historical Tracking | Long-term trends and benchmarks |
| 25 | `25-performance-apm-dashboard-page.png` | APM Dashboard | Application performance monitoring |
| 26 | `26-help-page.png` | Help | Help and documentation |

### Dark Mode Screenshots

| # | Filename | Page | Description |
|---|----------|------|-------------|
| 27 | `27-dashboard-dark-mode.png` | Dashboard (Dark) | Dashboard in dark mode |
| 28 | `28-characters-dark-mode.png` | Characters (Dark) | Characters list in dark mode |
| 29 | `29-welcome-dark-mode.png` | Welcome (Dark) | Landing page in dark mode |

---

## Key Features Demonstrated

### 1. **Dual Storage Architecture**

- Local mode (browser localStorage)
- Account mode (database-backed)
- Seamless conversion between modes

### 2. **Character Management**

- Character creation wizard with multi-step form
- Grid and list view layouts
- Stat tracking (Speed, Stamina, Power, Guts, Wisdom)
- Aptitude grades (G through S, S is maximum)
- Avatar management

### 3. **Training System**

- AI-powered training predictions
- Facility-based training options
- Stat gain calculations
- Risk assessment

### 4. **Race Management**

- Race scheduling
- Performance tracking
- Weather impact analysis
- Running style optimization

### 5. **Skill System**

- Comprehensive skill catalog
- Hint tracking (5 levels: 10%/20%/30%/35%/40% max SP reduction)
- Evolution paths (Normal → Rare)
- SP cost optimization

### 6. **Support Card System**

- 6-card deck builder
- Limit break multipliers (★-★★★★★)
- Meta tier rankings (SS, S, A, B)
- Bond level tracking

### 7. **AI Integration**

- Hybrid AI (Ollama + AWS Bedrock)
- Conversational AI advisor
- Training recommendations
- Race strategy analysis
- Skill optimization

### 8. **MCP Integration**

- Memory server (knowledge graph)
- Filesystem server (file operations)
- Fetch server (external APIs)
- GitKraken server (git operations)
- Chrome DevTools server (browser automation)
- Sequential Thinking server (advanced reasoning)

### 9. **Data Management**

- Import: JSON, CSV, Excel, Legacy formats
- Export: JSON, CSV, Excel, Markdown
- OCR screenshot processing
- Backup and restore
- Migration tools

### 10. **Performance Monitoring**

- APM dashboard
- API performance tracking
- Cache monitoring
- Query optimization
- Cost tracking

### 11. **Accessibility**

- WCAG 2.2 AA compliant
- Dark mode support
- Keyboard navigation
- Screen reader support
- Focus management

### 12. **Responsive Design**

- Mobile-first approach
- 320px - 2560px viewport support
- Touch-friendly interfaces
- Progressive Web App (PWA)

---

## UI/UX Highlights

### Design System

- **Colors:** Tailwind CSS v4 with custom stat colors
- **Typography:** System fonts with fallbacks
- **Icons:** Heroicons
- **Components:** Reusable Blade components
- **Interactivity:** Alpine.js for client-side reactivity

### Navigation

- Persistent sidebar navigation
- Breadcrumb trails
- Quick actions menu
- Search functionality
- User menu with profile access

### Feedback & Notifications

- Toast notifications
- Inline validation messages
- Loading states
- Error handling
- Success confirmations

### Data Visualization

- Stat progress bars
- Grade badges (G through S, S is maximum)
- Mood indicators
- Energy meters
- Progress tracking

---

## Technical Details

### Screenshot Specifications

- **Format:** PNG
- **Quality:** Full page screenshots
- **Resolution:** Native browser resolution
- **Browser:** Chrome (via Chrome DevTools MCP)
- **Viewport:** Desktop (1920x1080 equivalent)

### Capture Method

- Automated via Chrome DevTools MCP server
- Full page screenshots (not just viewport)
- Consistent lighting and rendering
- Both light and dark mode variants

---

## Usage Guidelines

### For Documentation

- Use these screenshots in README.md
- Include in user manuals
- Reference in PRDs and SPECs
- Add to presentation materials

### For Development

- Visual regression testing baseline
- UI/UX review reference
- Design system documentation
- Accessibility audit reference

### For Marketing

- Feature showcase
- Product demonstrations
- Social media content
- Landing page visuals

---

## Maintenance

### Update Schedule

- Capture new screenshots after major UI changes
- Update when new features are added
- Refresh after design system updates
- Maintain both light and dark mode variants

### Naming Convention

```text
[number]-[page-name]-[variant].png

Examples:
- 01-welcome-page.png
- 27-dashboard-dark-mode.png
- 06-characters-list-page.png
```

### Organization

- Sequential numbering for easy reference
- Descriptive filenames
- Grouped by feature area
- Dark mode variants at the end

---

## Related Documentation

- [README.md](../../README.md) - Main project documentation
- [Product Overview](../../docs/00-core-docs/product.md) - Product specifications
- [User Manual](../../docs/00-core-docs/017_SUM_Software_User_Manual.md) - User guide
- [Wireframes](../../docs/01-wireframes/) - UI/UX specifications
- [User Flows](../../docs/01-user-flows/) - User journey diagrams

---

**Last Updated:** January 26, 2026  
**Captured By:** Automated Chrome DevTools MCP  
**Application Version:** 2.0.0
