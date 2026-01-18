# Offline Page Testing Guide

## Overview

The application now includes a styled offline page that displays when users lose their internet connection. This page provides a better user experience than the default blank page with plain text.

## Features

### Visual Design

- **Gradient background** that matches the app's color scheme
- **App logo** with pulse animation
- **Status badge** with blinking indicator
- **Retry button** with loading state
- **Helpful tips** for troubleshooting connection issues
- **Dark mode support** using CSS media queries
- **Responsive design** for mobile and desktop

### Functionality

- **Auto-retry**: Checks connection every 5 seconds and auto-reloads when online
- **Manual retry**: Users can click "Try Again" button to check connection
- **Online event listener**: Automatically reloads when browser detects connection
- **Loading states**: Visual feedback during connection checks

## Testing the Offline Page

### Method 1: Direct Access

1. Navigate to `http://127.0.0.1:8000/offline.html` in your browser
2. You should see the styled offline page

### Method 2: Chrome DevTools (Recommended)

1. Open the application in Chrome
2. Open DevTools (F12)
3. Go to the **Network** tab
4. Check the **Offline** checkbox at the top
5. Refresh the page or navigate to a new page
6. The offline page should appear

### Method 3: Service Worker Simulation

1. Open the application in Chrome
2. Open DevTools (F12)
3. Go to the **Application** tab
4. Click on **Service Workers** in the left sidebar
5. Check the **Offline** checkbox
6. Navigate to a new page
7. The offline page should appear

### Method 4: Disable Network

1. Disconnect from Wi-Fi or disable network adapter
2. Try to navigate in the application
3. The offline page should appear

## Expected Behavior

### When Offline

- User sees a styled page with:
  - App logo (if cached)
  - "You're Offline" heading
  - Status badge showing "Offline"
  - Helpful message
  - "Try Again" button
  - Troubleshooting tips

### When Connection Restored

- Page automatically reloads within 5 seconds
- Or user can click "Try Again" to immediately check connection
- Loading state shows "Checking..." during retry

## Service Worker Integration

The offline page is cached during service worker installation and served when:

1. Network request fails
2. No cached version of the requested page exists
3. User is navigating (not requesting API or assets)

## Files Modified

- `public/offline.html` - New styled offline page
- `public/sw.js` - Updated to cache offline.html during installation

## Browser Compatibility

The offline page works in all modern browsers that support:

- Service Workers
- CSS Grid/Flexbox
- CSS Custom Properties
- ES6 JavaScript

Tested in:

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Accessibility

The offline page includes:

- Semantic HTML structure
- Proper heading hierarchy
- Alt text for images
- Sufficient color contrast
- Keyboard-accessible retry button
- Screen reader friendly content

## Dark Mode

The page automatically adapts to the user's system preference:

- Light mode: White card with purple gradient background
- Dark mode: Dark card with blue-purple gradient background

Uses `@media (prefers-color-scheme: dark)` CSS media query.

## Future Enhancements

Potential improvements:

- Show cached pages list
- Offline data sync queue
- Network speed indicator
- Estimated time to reconnect
- Offline-capable features list
