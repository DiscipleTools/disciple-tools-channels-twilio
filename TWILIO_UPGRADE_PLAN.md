# Twilio SDK Upgrade Plan: 6.44.4 → 8.6.3

## Overview
This document outlines the incremental upgrade strategy for the Twilio PHP SDK from version 6.44.4 to 8.6.3, addressing [GitHub Issue #27](https://github.com/DiscipleTools/disciple-tools-channels-twilio/issues/27).

## Current State
- **Current Version:** 6.44.4
- **Target Version:** 8.6.3  
- **PHP Requirements:** Current: >=7.1.0, Target: >=7.2.0

## Features Used (Audit)
Based on code analysis, the plugin uses:
- ✅ Basic Client initialization (`new Client($sid, $token)`)
- ✅ Messages API (`$client->messages->create()`)
- ✅ Messaging Services (`$client->messaging->v1->services()`)
- ✅ Phone Numbers (`$client->incomingPhoneNumbers->read()`)
- ✅ Request Validation (`RequestValidator`)
- ✅ Content API (via HTTP requests)

## Incremental Upgrade Strategy

### Phase 1: Pre-Upgrade Testing ✅
**Status: COMPLETE** - Already on 6.44.4 (latest 6.x)

### Phase 2: Upgrade to 7.x 🔄
**Target:** 6.44.4 → 7.16.2

#### Steps:
1. **Update composer.json** ✅
   ```json
   "twilio/sdk": "^7.16"
   ```

2. **Run Composer Update**
   ```bash
   composer update twilio/sdk
   ```

3. **Test Core Functionality**
   - [ ] SMS sending
   - [ ] WhatsApp messaging  
   - [ ] Webhook processing
   - [ ] Phone number listing
   - [ ] Messaging services
   - [ ] Template functionality

4. **Check for Deprecation Warnings**
   - Monitor PHP error logs
   - Test all Twilio-dependent features

#### Potential Breaking Changes in 7.x:
- Method signature changes
- Parameter validation improvements
- Possible namespace adjustments

### Phase 3: Upgrade to 8.x 📋
**Target:** 7.16.2 → 8.6.3

#### Steps:
1. **Update composer.json**
   ```json
   "twilio/sdk": "^8.6"
   ```

2. **Run Composer Update**
   ```bash
   composer update twilio/sdk
   ```

3. **Comprehensive Testing**
   - All functionality from Phase 2
   - Performance testing
   - Error handling verification

#### Potential Breaking Changes in 8.x:
- Major API restructuring
- Type declarations
- HTTP client changes

## Testing Checklist

### Core Functionality Tests
- [ ] **Client Initialization**
  ```php
  $client = new Client($sid, $token);
  ```

- [ ] **Send SMS**
  ```php
  $client->messages->create($phone, ['from' => $from, 'body' => $message]);
  ```

- [ ] **Send WhatsApp**
  ```php
  $client->messages->create('whatsapp:' . $phone, $options);
  ```

- [ ] **List Phone Numbers**
  ```php
  $client->incomingPhoneNumbers->read([], 20);
  ```

- [ ] **Messaging Services**
  ```php
  $client->messaging->v1->services($sid)->phoneNumbers->read(20);
  ```

- [ ] **Webhook Validation**
  ```php
  $validator = new RequestValidator($token);
  $validator->validate($signature, $url, $params);
  ```

### Integration Tests
- [ ] Notification system integration
- [ ] Magic link integration
- [ ] Contact record commenting
- [ ] Template message sending

## Rollback Plan

### If Issues Found in Phase 2:
```bash
# Revert to 6.x
composer require twilio/sdk:^6.44
composer update
```

### If Issues Found in Phase 3:
```bash
# Revert to 7.x
composer require twilio/sdk:^7.16
composer update
```

## PHP Version Compatibility

### Current Support:
- PHP 7.1+ (Twilio 6.x)

### After Upgrade:
- PHP 7.2+ (Twilio 8.x)
- Full support: PHP 7.2, 7.3, 7.4, 8.0, 8.1, 8.2, 8.3, 8.4

## Environment Setup

### Development Environment:
```bash
# Create backup
cp -r wp-content/plugins/disciple-tools-channels-twilio /backup/location/

# Run upgrade
cd wp-content/plugins/disciple-tools-channels-twilio
composer update twilio/sdk

# Test functionality
php test-twilio-functions.php
```

### Staging Environment:
1. Deploy upgraded plugin
2. Run full test suite
3. Monitor for 24-48 hours
4. Check error logs

### Production Deployment:
1. Schedule maintenance window
2. Create database backup
3. Deploy to production
4. Monitor system health
5. Verify all Twilio integrations

## Success Criteria

### Phase 2 (7.x) Success:
- [ ] All existing functionality works
- [ ] No PHP errors or warnings
- [ ] Performance maintained
- [ ] Webhook processing unaffected

### Phase 3 (8.x) Success:
- [ ] All Phase 2 criteria met
- [ ] Latest security patches applied
- [ ] Future compatibility ensured
- [ ] Performance improvements utilized

## Timeline Estimate

- **Phase 2 (7.x):** 2-3 days (testing focused)
- **Phase 3 (8.x):** 3-5 days (comprehensive testing)
- **Total:** 1-2 weeks with proper staging

## Contact Points

- **Primary Issue:** [#27](https://github.com/DiscipleTools/disciple-tools-channels-twilio/issues/27)
- **Twilio PHP Docs:** [https://www.twilio.com/docs/libraries/php](https://www.twilio.com/docs/libraries/php)
- **Upgrade Guide:** [https://github.com/twilio/twilio-php/blob/main/UPGRADE.md](https://github.com/twilio/twilio-php/blob/main/UPGRADE.md)

---

**Last Updated:** `date +%Y-%m-%d`  
**Status:** Phase 2 Ready 