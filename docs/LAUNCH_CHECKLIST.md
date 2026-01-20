# Launch Checklist

## Pre-Launch Verification

This checklist ensures all requirements are met before deploying to production.

---

## 1. Code Quality

### Static Analysis

- [ ] PHPStan analysis passes at level 5+
- [ ] Laravel Pint formatting applied (`vendor/bin/pint`)
- [ ] No critical security vulnerabilities in dependencies
- [ ] All TODO/FIXME comments addressed or documented

### Code Review

- [ ] All feature branches merged to main
- [ ] Code review completed for all changes
- [ ] No merge conflicts pending
- [ ] Git history is clean

---

## 2. Testing

### Unit Tests

- [ ] All unit tests passing (`php artisan test --testsuite=Unit`)
- [ ] Code coverage meets minimum threshold (80%+)
- [ ] No skipped or incomplete tests

### Feature Tests

- [ ] All feature tests passing (`php artisan test --testsuite=Feature`)
- [ ] API endpoint tests verified
- [ ] Authentication flows tested
- [ ] Authorization policies tested

### Integration Tests

- [ ] Database operations verified
- [ ] External API integrations tested
- [ ] Queue processing verified
- [ ] Cache operations tested

### Performance Tests

- [ ] Response time benchmarks met (<200ms average)
- [ ] No N+1 query issues detected
- [ ] Memory usage within limits
- [ ] Concurrent user load tested

### Security Tests

- [ ] CSRF protection verified
- [ ] XSS prevention tested
- [ ] SQL injection prevention verified
- [ ] Authentication security tested
- [ ] Rate limiting configured

### Accessibility Tests

- [ ] WCAG 2.2 AA compliance verified
- [ ] Screen reader compatibility tested
- [ ] Keyboard navigation working
- [ ] Color contrast ratios met

---

## 3. Database

### Schema

- [ ] All migrations applied successfully
- [ ] Database indexes optimized
- [ ] Foreign key constraints verified
- [ ] No orphaned records

### Data

- [ ] Production seeders prepared
- [ ] Reference data populated
- [ ] Test data removed
- [ ] Data integrity verified

### Backup

- [ ] Backup procedures tested
- [ ] Restore procedures verified
- [ ] Backup schedule configured
- [ ] Retention policy set

---

## 4. Infrastructure

### Server Configuration

- [ ] PHP 8.4+ installed and configured
- [ ] Required PHP extensions enabled
- [ ] Web server (Apache/Nginx) configured
- [ ] SSL certificate installed and valid

### Environment

- [ ] Production `.env` configured
- [ ] APP_ENV set to `production`
- [ ] APP_DEBUG set to `false`
- [ ] APP_KEY generated and secured
- [ ] Database credentials secured

### Caching

- [ ] Redis/cache server configured
- [ ] Cache driver set appropriately
- [ ] Session driver configured
- [ ] Queue driver configured

### Storage

- [ ] Storage directories writable
- [ ] Storage link created (`php artisan storage:link`)
- [ ] File upload limits configured
- [ ] Disk space adequate

---

## 5. Security

### Application Security

- [ ] HTTPS enforced
- [ ] Secure headers configured
- [ ] CORS policy set
- [ ] Rate limiting enabled
- [ ] API authentication configured

### Secrets Management

- [ ] All secrets in environment variables
- [ ] No hardcoded credentials
- [ ] API keys rotated
- [ ] Encryption keys secured

### Access Control

- [ ] Admin access restricted
- [ ] Telescope/Horizon protected
- [ ] Debug routes disabled
- [ ] Error pages don't leak info

---

## 6. Performance

### Optimization

- [ ] Config cached (`php artisan config:cache`)
- [ ] Routes cached (`php artisan route:cache`)
- [ ] Views cached (`php artisan view:cache`)
- [ ] Events cached (`php artisan event:cache`)
- [ ] Autoloader optimized (`composer install --optimize-autoloader`)

### Assets

- [ ] Frontend assets built (`npm run build`)
- [ ] Assets minified
- [ ] Images optimized
- [ ] CDN configured (if applicable)

### Database

- [ ] Query optimization verified
- [ ] Indexes analyzed
- [ ] Connection pooling configured
- [ ] Slow query logging enabled

---

## 7. Monitoring

### Error Tracking

- [ ] Exception handler configured
- [ ] Error notifications set up
- [ ] Log rotation configured
- [ ] Log levels appropriate

### Performance Monitoring

- [ ] APM tool configured (if applicable)
- [ ] Health check endpoint working
- [ ] Metrics collection enabled
- [ ] Alerting thresholds set

### Uptime Monitoring

- [ ] External monitoring configured
- [ ] Alert contacts verified
- [ ] Escalation procedures documented

---

## 8. Documentation

### User Documentation

- [ ] User guide complete
- [ ] FAQ updated
- [ ] Help content accessible
- [ ] Contact information current

### Technical Documentation

- [ ] API documentation current
- [ ] Deployment guide updated
- [ ] Architecture docs current
- [ ] Runbook prepared

### Operations

- [ ] Incident response plan documented
- [ ] Rollback procedures documented
- [ ] On-call rotation set
- [ ] Communication plan ready

---

## 9. Deployment

### CI/CD

- [ ] Deployment pipeline tested
- [ ] Staging deployment successful
- [ ] Production deployment tested (dry run)
- [ ] Rollback tested

### Database Migration

- [ ] Migration plan documented
- [ ] Rollback scripts prepared
- [ ] Data migration tested
- [ ] Downtime window scheduled (if needed)

### Communication

- [ ] Stakeholders notified
- [ ] Maintenance window announced
- [ ] Support team briefed
- [ ] Status page updated

---

## 10. Post-Launch

### Verification

- [ ] Application accessible
- [ ] Core features working
- [ ] No critical errors in logs
- [ ] Performance metrics normal

### Monitoring

- [ ] Error rates normal
- [ ] Response times acceptable
- [ ] Resource usage stable
- [ ] No security alerts

### Communication

- [ ] Launch announced
- [ ] Status page updated
- [ ] Support channels active
- [ ] Feedback collection enabled

---

## Rollback Procedures

### Immediate Rollback (< 5 minutes)

If critical issues are detected immediately after deployment:

```bash
# 1. Put application in maintenance mode
php artisan down --retry=60

# 2. Restore previous deployment
cd /var/www/html
tar -xzf ../backup-YYYYMMDD-HHMMSS.tar.gz

# 3. Rollback database if needed
php artisan migrate:rollback --step=1

# 4. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 5. Bring application back up
php artisan up
```

### Planned Rollback (> 5 minutes)

For non-critical issues that require investigation:

1. **Assess the situation**
   - Identify affected functionality
   - Determine user impact
   - Evaluate rollback necessity

2. **Communicate**
   - Notify stakeholders
   - Update status page
   - Inform support team

3. **Execute rollback**
   - Follow immediate rollback steps
   - Verify rollback success
   - Monitor for issues

4. **Post-mortem**
   - Document the issue
   - Identify root cause
   - Plan remediation

---

## Emergency Contacts

| Role | Name | Contact |
|------|------|---------|
| Technical Lead | [Name] | [Email/Phone] |
| DevOps Lead | [Name] | [Email/Phone] |
| Product Owner | [Name] | [Email/Phone] |
| On-Call Engineer | [Rotation] | [PagerDuty] |

---

## Sign-Off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Development Lead | | | |
| QA Lead | | | |
| DevOps Lead | | | |
| Product Owner | | | |

---

## Version History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2026-01-20 | System | Initial checklist |
