# VIODCIM Modernization Roadmap

## Executive Summary

This roadmap outlines the path to modernize the VIODCIM DCIM project from its current PHP 5.5-7.0 state to PHP 8.3+ with contemporary development practices.

**Current State:**
- PHP 5.5-7.0 compatible codebase
- ~1,530 PHP files, ~80,000 lines of code
- jQuery + Bootstrap 3 frontend
- PDO database layer (inconsistent usage)
- Slim Framework v2 for API

**Target State:**
- PHP 8.3+ with strict typing
- Modern frontend (Vue/React or modern jQuery)
- Consistent PDO with prepared statements
- RESTful API with OpenAPI documentation
- Docker-based deployment

---

## Phase 1: Security & PHP 8.x Compatibility (Weeks 1-4)

### 1.1 Critical Security Fixes (Week 1)

**Priority: CRITICAL**

| Task | Files | Effort |
|------|-------|--------|
| Move credentials to environment variables | `db.inc.php` | 2 hours |
| Fix SQL injection in config.inc.php | `config.inc.php` | 4 hours |
| Add CSRF tokens to all forms | All form files | 8 hours |
| Fix XSS vulnerabilities | `device.php`, `asset.php`, `rack.php` | 6 hours |

**Quick Fixes:**
```php
// db.inc.php - BEFORE
dbpass = 'Admin1@#4';

// db.inc.php - AFTER
dbpass = getenv('DB_PASSWORD') ?: 'default_dev_only';
```

### 1.2 PHP 8.0 Compatibility (Weeks 2-3)

**Priority: HIGH**

| Issue | Count | Solution |
|-------|-------|----------|
| `each()` function | ~736 | Replace with `foreach()` |
| `var $property` | ~500 | Change to `public $property` |
| Old constructors | ~100 | Change to `__construct()` |
| `error_reporting(0)` | 1 | Remove or use proper logging |

**Migration Script:**
```bash
# Replace each() with foreach
find . -name "*.php" -exec sed -i 's/while.*each(\$/_tmp_&/g' {} \;
# Then manual review for complex cases
```

### 1.3 Database Layer Hardening (Week 4)

**Priority: HIGH**

- Convert all direct SQL to prepared statements
- Add input validation layer
- Implement database transaction wrapper

---

## Phase 2: Code Quality & Architecture (Weeks 5-8)

### 2.1 Class Modernization

**Priority: MEDIUM**

```php
// BEFORE
class Device {
    var $DeviceID;
    var $Label;
    
    function Device($id=false) { }
    function MakeSafe() { }
}

// AFTER
class Device {
    public int $DeviceID;
    public string $Label;
    
    public function __construct(?int $id = null) { }
    public function sanitize(): void { }
}
```

### 2.2 Dependency Injection

**Priority: MEDIUM**

- Replace global `$dbh` with injected Database connection
- Create Service Container
- Implement Repository pattern for data access

### 2.3 Error Handling

**Priority: MEDIUM**

- Implement proper exception handling
- Add logging framework (Monolog)
- Create error boundaries

---

## Phase 3: Frontend Modernization (Weeks 9-14)

### 3.1 Short Term: jQuery Updates

**Priority: MEDIUM**

- Update jQuery to 3.7.x
- Update Bootstrap to 5.x
- Update DataTables to latest
- Add CSP headers

### 3.2 Long Term: Modern Framework (Optional)

**Priority: LOW**

Consider gradual migration to:
- Vue 3 + Inertia.js (progressive enhancement)
- Or React components for complex UI
- Keep PHP backend, modernize frontend selectively

---

## Phase 4: API Improvements (Weeks 12-16)

### 4.1 API Standardization

**Priority: MEDIUM**

- Upgrade Slim Framework to v4
- Implement JWT authentication
- Add rate limiting
- Create OpenAPI/Swagger documentation

### 4.2 Response Format Standardization

```php
// Standard API Response
{
    "status": "success|error",
    "data": { ... },
    "meta": {
        "page": 1,
        "per_page": 20,
        "total": 100
    },
    "message": "Human-readable message"
}
```

---

## Phase 5: DevOps & Deployment (Weeks 16-20)

### 5.1 Docker Production Setup

**Priority: HIGH**

- Multi-stage Dockerfile
- Docker Compose for production
- Health checks
- Log aggregation

### 5.2 CI/CD Pipeline

**Priority: MEDIUM**

- GitHub Actions or GitLab CI
- Automated testing
- Security scanning
- Deployment automation

### 5.3 Monitoring

**Priority: MEDIUM**

- Application monitoring (Sentry)
- Server monitoring (Prometheus/Grafana)
- Database performance monitoring

---

## Technology Recommendations

| Category | Current | Recommended |
|----------|---------|-------------|
| PHP | 5.5-7.0 | 8.3+ |
| Database | MySQL 5.x | MySQL 8.0 |
| Web Server | Apache | Nginx + PHP-FPM |
| Frontend | jQuery 1.x | jQuery 3.x or Vue 3 |
| CSS | Bootstrap 3 | Bootstrap 5 or Tailwind |
| API | Slim v2 | Slim v4 |
| Testing | None | PHPUnit + Pest |
| Documentation | None | OpenAPI/Swagger |

---

## Effort Estimates

| Phase | Duration | Developer Hours |
|-------|----------|-----------------|
| Phase 1: Security & PHP 8.x | 4 weeks | 160 hours |
| Phase 2: Code Quality | 4 weeks | 160 hours |
| Phase 3: Frontend | 6 weeks | 240 hours |
| Phase 4: API | 4 weeks | 160 hours |
| Phase 5: DevOps | 4 weeks | 160 hours |
| **Total** | **22 weeks** | **880 hours** |

---

## Quick Wins (Can be done immediately)

1. **Fix hardcoded credentials** - 2 hours
2. **Enable error reporting in dev** - 30 minutes
3. **Add basic CSRF tokens** - 4 hours
4. **Update jQuery to 3.7** - 2 hours
5. **Add security headers** - 1 hour

---

## Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|------------|--------|------------|
| Breaking changes during PHP upgrade | High | High | Comprehensive testing, feature flags |
| Database migration issues | Medium | High | Backup strategy, rollback plan |
| User resistance to UI changes | Medium | Medium | Gradual rollout, training |
| Third-party integration breakage | Medium | Medium | API versioning, compatibility layer |

---

## Recommended Approach

1. **Start with Phase 1** - Security fixes are non-negotiable
2. **Use feature flags** - Allow gradual rollout
3. **Maintain backward compatibility** - During transition period
4. **Write tests as you go** - Focus on critical paths first
5. **Document everything** - API docs, migration guides

---

## Next Steps

1. Review and prioritize security fixes
2. Set up development environment with Docker
3. Create feature branch for PHP 8.x migration
4. Begin with `each()` to `foreach()` conversion
5. Add PHPUnit test suite
